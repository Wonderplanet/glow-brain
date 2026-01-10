require 'rails_helper'

RSpec.describe "OprInAppProductTranslator" do
  subject { OprInAppProductTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, product_type: 1, mst_in_app_product_id: 2, start_at: 3, end_at: 4, is_sale: 5, icon_asset_key: 6, banner_path: 7, limit_amount: 8, description: 9, sort_number: 10)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprInAppProductViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.product_type).to eq use_case_data.product_type
      expect(view_model.mst_in_app_product_id).to eq use_case_data.mst_in_app_product_id
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
      expect(view_model.is_sale).to eq use_case_data.is_sale
      expect(view_model.icon_asset_key).to eq use_case_data.icon_asset_key
      expect(view_model.banner_path).to eq use_case_data.banner_path
      expect(view_model.limit_amount).to eq use_case_data.limit_amount
      expect(view_model.description).to eq use_case_data.description
      expect(view_model.sort_number).to eq use_case_data.sort_number
    end
  end
end
