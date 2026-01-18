require 'rails_helper'

RSpec.describe "InAppPurchaseHistoryTranslator" do
  subject { InAppPurchaseHistoryTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_in_app_product_id: 0, total: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(InAppPurchaseHistoryViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_in_app_product_id).to eq use_case_data.opr_in_app_product_id
      expect(view_model.total).to eq use_case_data.total
    end
  end
end
