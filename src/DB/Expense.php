<?php
namespace Chama\DB;
use Chama\DB\Chama;
use Chama\DB\DbManager;
use Chama\Utils\Money;

/**
 * Expense
 * Manages all the expense functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class Expense {
	private $db;
	public function __construct() {
		$this->db = DbManager::connect();
	}
	public function forChama($chamaId, $page = 1) {
		$this->db->join("users", "chama_expenses.posted_by = users.user_id", "LEFT");
		$this->db->where("chama_expenses.chama_id", $chamaId);
		$data = $this->db->paginate("chama_expenses", $page, "chama_expenses.amount, chama_expenses.description, chama_expenses.date, chama_expenses.id AS expense_id, users.name,users.phonenumber,users.user_id");

		$out = array();

		foreach ($data as $expense) {
			$expense["amount"] = Money::format('%i', $expense["amount"]);
			$out[] = $expense;
		}
		return $out;
	}
	public function sumForChama($chamaId) {
		$this->db->where('chama_id', $chamaId);
		$result = $this->db->getOne("chama_expenses", "SUM(amount) as amount");

		return (is_null($result['amount'])) ? 0 : $result['amount'];

	}
	public function single($expenseId) {
		$this->db->join("users", "chama_expenses.posted_by = users.user_id", "LEFT");
		$this->db->where("id", $expenseId);
		$expense = $this->db->getOne("chama_expenses", "chama_expenses.amount, chama_expenses.description, chama_expenses.date, chama_expenses.id AS expense_id, users.name as posted_by,users.phonenumber,users.user_id");

		$expense["decorated_amount"] = Money::format('%i', $expense["amount"]);
		return $expense;

	}
	public function create($chamaId, $amount, $description, $day, $month, $year, $posted_by) {

		$id = $this->db->insert('chama_expenses', array(
			'chama_id' => $chamaId,
			'amount' => $amount,
			'description' => $description,
			'day' => $day,
			'month' => $month,
			'year' => $year,
			'posted_by' => $posted_by,
		));
		return $id;
	}
	public function update($expenseId, $amount, $description, $date) {
		$this->db->where("id", $expenseId);
		$data = Array(
			'amount' => $amount,
			'description' => $description,
			'date' => $date,
		);
		return $this->db->update('chama_expenses', $data);
	}
	public function delete($expenseId) {
		$this->db->where('id', $expenseId);
		return $this->db->delete('chama_expenses');

	}
	public function deleteAllForChama($chamaId) {
		$this->db->where("chama_id", $chamaId);
		return $this->db->delete('chama_expenses');
	}

	public function search($chamaId, $month, $year) {
		$this->db->where("chama_id", $chamaId);
		$this->db->where("month", $month);
		$this->db->where("year", $year);
		$data = $this->db->paginate("chama_expenses", 1, "amount, description, date, chama_expenses.id AS expense_id");

		$out = array();

		foreach ($data as $expense) {
			$expense["decorated_amount"] = Money::format('%i', $expense["amount"]);
			$out[] = $expense;
		}
		return $out;
	}
}