require 'rails_helper'

RSpec.describe "PrizeTranslator" do
  subject { PrizeTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, prize_type: 0, mst_character_variant_id: 1, mst_item_id: 2, item_amount: 3, crystal_amount: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(PrizeViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.prize_type).to eq use_case_data.prize_type
      expect(view_model.mst_character_variant_id).to eq use_case_data.mst_character_variant_id
      expect(view_model.mst_item_id).to eq use_case_data.mst_item_id
      expect(view_model.item_amount).to eq use_case_data.item_amount
      expect(view_model.crystal_amount).to eq use_case_data.crystal_amount
    end
  end
end
