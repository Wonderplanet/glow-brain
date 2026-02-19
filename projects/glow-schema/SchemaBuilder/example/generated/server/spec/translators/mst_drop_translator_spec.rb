require 'rails_helper'

RSpec.describe "MstDropTranslator" do
  subject { MstDropTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_puzzle_opponent_id: 1, mst_item_id: 2, amount: 3, relative_probability: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstDropViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_puzzle_opponent_id).to eq use_case_data.mst_puzzle_opponent_id
      expect(view_model.mst_item_id).to eq use_case_data.mst_item_id
      expect(view_model.amount).to eq use_case_data.amount
      expect(view_model.relative_probability).to eq use_case_data.relative_probability
    end
  end
end
