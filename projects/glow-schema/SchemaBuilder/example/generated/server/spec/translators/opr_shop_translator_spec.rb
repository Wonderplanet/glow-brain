require 'rails_helper'

RSpec.describe "OprShopTranslator" do
  subject { OprShopTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, opr_shop_category_id: 1, name: 2, description: 3, price: 4, price_type: 5, price_mst_item_id: 6, is_sale: 7, purchasable_count: 8, banner_path: 9, icon_asset_key: 10, sort_number: 11, opr_event_id: 12, start_at: 13, end_at: 14)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprShopViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.opr_shop_category_id).to eq use_case_data.opr_shop_category_id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.description).to eq use_case_data.description
      expect(view_model.price).to eq use_case_data.price
      expect(view_model.price_type).to eq use_case_data.price_type
      expect(view_model.price_mst_item_id).to eq use_case_data.price_mst_item_id
      expect(view_model.is_sale).to eq use_case_data.is_sale
      expect(view_model.purchasable_count).to eq use_case_data.purchasable_count
      expect(view_model.banner_path).to eq use_case_data.banner_path
      expect(view_model.icon_asset_key).to eq use_case_data.icon_asset_key
      expect(view_model.sort_number).to eq use_case_data.sort_number
      expect(view_model.opr_event_id).to eq use_case_data.opr_event_id
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
    end
  end
end
