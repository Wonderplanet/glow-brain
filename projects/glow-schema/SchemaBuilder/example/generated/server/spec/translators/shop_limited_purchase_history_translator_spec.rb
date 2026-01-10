require 'rails_helper'

RSpec.describe "ShopLimitedPurchaseHistoryTranslator" do
  subject { ShopLimitedPurchaseHistoryTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_shop_id: 0, count: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(ShopLimitedPurchaseHistoryViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_shop_id).to eq use_case_data.opr_shop_id
      expect(view_model.count).to eq use_case_data.count
    end
  end
end
