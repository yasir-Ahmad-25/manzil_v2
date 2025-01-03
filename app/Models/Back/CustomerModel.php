<?php

namespace App\Models\Back;

use App\Models\Back\FinancialModel;
use CodeIgniter\Model;

class CustomerModel extends Model
{
 
    public function store($table, $data)
    {
        $this->db->table($table)->insert($data);
        return $this->db->insertID();
    }

    public function get_outstanding_deposits($cust_id){
        return $this->db->query("SELECT sum(amount_bal) as total_deposit from tbl_deposit where customer_id=$cust_id")->getRow();
    }
    public function get_advances(){
        return $this->db->query("SELECT ca.*, cu.cust_name from tbl_clients_advance ca join tbl_customers cu on cu.customer_id=ca.tenant_id")->getResultArray();
    }

    public function update_table($table, $data)
    {
        $this->db->table($table)->upsertBatch($data);
        return true;
    }
    public function get_customers($brid)
    {
            // brid stands for branch_id
            return $this->fetchCustomerBasedOnBranch($brid);
    }

    public function fetchCustomerBasedOnBranch($brid){
        if($brid !== 1){
            $sql = "SELECT DISTINCT cu.*, (SELECT SUM(amount) FROM tbl_deposit td WHERE td.customer_id = cu.customer_id) AS total_deposit,acc.acc_balance FROM tbl_customers cu JOIN tbl_cl_accounts acc ON cu.customer_id = acc.acc_tag WHERE acc.acc_set = 'Customer' AND cu.branch_id =".$brid;
            $query = $this->db->query($sql);
            return $query->getResultArray();
        }else{
            // then it's admin
            $sql = "SELECT DISTINCT cu.*, (SELECT SUM(amount) FROM tbl_deposit td WHERE td.customer_id = cu.customer_id) AS total_deposit,acc.acc_balance FROM tbl_customers cu JOIN tbl_cl_accounts acc ON cu.customer_id = acc.acc_tag WHERE acc.acc_set = 'Customer'";
            $query = $this->db->query($sql);
            return $query->getResultArray();
        }

        
    }
    // IN CASE LOO BAAHDO IN LASOO AQRIYO CUSTOMERS KA AYADOO LOO FIIRINAAYO PROPERTIES KA AY KALA DAGAN YIHIIN AMA SITE/BUILDING

    public function get_customers_based_on_site($selectedSite,$brid)
    {
            // brid stands for branch_id
            return $this->fetchCustomerBasedOnSite($selectedSite,$brid);
    }
    
    public function fetchCustomerBasedOnSite($selectedSite , $brid){
        if($brid !== 1){
            if($selectedSite !== "All_Sites"){
                // fetch customers based on selected site
                $sql = "SELECT DISTINCT cu.*, (SELECT SUM(amount) FROM tbl_deposit td WHERE td.customer_id = cu.customer_id) AS total_deposit,acc.acc_balance FROM tbl_customers cu JOIN tbl_cl_accounts acc ON cu.customer_id = acc.acc_tag JOIN tbl_sites s ON cu.site_id = s.site_id WHERE acc.acc_set = 'Customer' AND cu.branch_id =".$brid." AND s.site_id =".$selectedSite;
                $query = $this->db->query($sql);
                return $query->getResultArray();
            }else{
                // fetch all 
                $sql = "SELECT DISTINCT cu.*, (SELECT SUM(amount) FROM tbl_deposit td WHERE td.customer_id = cu.customer_id) AS total_deposit,acc.acc_balance FROM tbl_customers cu JOIN tbl_cl_accounts acc ON cu.customer_id = acc.acc_tag WHERE acc.acc_set = 'Customer' AND cu.branch_id =".$brid;
                $query = $this->db->query($sql);
                return $query->getResultArray();
            }
        }else{
            // then it's admin
            if($selectedSite !== "All_Sites"){
                // fetch All customer based on selected site
                $sql = "SELECT DISTINCT cu.*, (SELECT SUM(amount) FROM tbl_deposit td WHERE td.customer_id = cu.customer_id) AS total_deposit,acc.acc_balance FROM tbl_customers cu JOIN tbl_cl_accounts acc ON cu.customer_id = acc.acc_tag JOIN tbl_sites s ON cu.site_id = s.site_id WHERE acc.acc_set = 'Customer' AND s.site_id =".$selectedSite;
                $query = $this->db->query($sql);
                return $query->getResultArray();
            }else{
                // fetch all 
                $sql = "SELECT DISTINCT cu.*, (SELECT SUM(amount) FROM tbl_deposit td WHERE td.customer_id = cu.customer_id) AS total_deposit,acc.acc_balance FROM tbl_customers cu JOIN tbl_cl_accounts acc ON cu.customer_id = acc.acc_tag WHERE acc.acc_set = 'Customer'";
                $query = $this->db->query($sql);
                return $query->getResultArray();
            }
        }

        
    }

    public function getsiteFromDB($site_id){
        $branch_id = (int) session()->get('user')['branch_id']; 
        if($branch_id !== 1){
            $sql = 'SELECT * FROM tbl_sites WHERE No_of_Floors > 0 AND branch_id ='.$branch_id.' AND site_id='.$site_id;
            $query = $this->db->query($sql);
            return $query->getResultArray();
        }else{
            $sql = 'SELECT * FROM tbl_sites WHERE No_of_Floors > 0 AND site_id='.$site_id;
            $query = $this->db->query($sql);
            return $query->getResultArray();
        }
    }

    public function get_customers_()
    {
        $brid = session()->get('user')['branch_id'];

        $query = $this->db->query("SELECT * FROM `tbl_customers` WHERE branch_id='$brid'");
        return $query->getResultArray();
    }

    public function get_customer_bal($cid)
    {
        $result = $this->db->query("SELECT balance FROM tbl_customers WHERE customer_id='$cid'");
        return $result->getRow()->balance ?? 0;
    }

    public function get_cust_history($tid)
    {
        $sql = "select * from tbl_customer_balances where customer_id='$tid'";

        $query = $this->db->query($sql);
        return $query->getResultArray();
    }


    public function get_cust_info($sid)
    {
        $sql = "select * from tbl_customers where customer_id='$sid'";
        $query = $this->db->query($sql);
        return $query->getRow();
    }
    public function customer_exists($name)
    {
        return $this->db->query("SELECT * FROM tbl_customers where cust_name='$name'")->getNumRows() > 0;
    }


    public function receivables_report()
    {
        $brid = session()->get('user')['branch_id'];

        $sql = "SELECT sp.cust_name, sp.cust_tell, acc_balance FROM tbl_customers sp 
        JOIN tbl_cl_accounts acc ON sp.customer_id=acc.acc_tag AND acc.acc_set='Customer' and sp.branch_id='$brid'";
        $query = $this->db->query($sql);
        return $query->getResultArray();
    }
}
