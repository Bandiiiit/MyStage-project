<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();  // Make sure to load the database
    }

    // Function to get a transaction by its ID
    public function get_transaction($id) {
        // Ensure that the 'invoices' table exists and replace with your actual table name
        $this->db->where('id', $id);  // Find the transaction with the given ID
        $query = $this->db->get('invoices');  // Query the 'invoices' table
        return $query->row();  // Return a single result row as an object
    }
}
