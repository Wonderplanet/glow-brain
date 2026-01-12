require 'rails_helper'

RSpec.describe "OprShopItemTranslator" do
  subject { OprShopItemTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_shop_id: 0, mst_item_id: 1, amount: 2, mst_character_variant_id: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprShopItemViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_shop_id).to eq use_case_data.opr_shop_id
      expect(view_model.mst_item_id).to eq use_case_data.mst_item_id
      expect(view_model.amount).to eq use_case_data.amount
      expect(view_model.mst_character_variant_id).to eq use_case_data.mst_character_variant_id
    end
  end
end
