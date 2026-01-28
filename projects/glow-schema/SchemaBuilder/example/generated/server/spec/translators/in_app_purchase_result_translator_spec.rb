require 'rails_helper'

RSpec.describe "InAppPurchaseResultTranslator" do
  subject { InAppPurchaseResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, received: 0, in_app_purchase_history: 1, subscription_pass: 2, updated_mission: 3, sum_currency: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(InAppPurchaseResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.received).to eq use_case_data.received
      expect(view_model.in_app_purchase_history).to eq use_case_data.in_app_purchase_history
      expect(view_model.subscription_pass).to eq use_case_data.subscription_pass
      expect(view_model.updated_mission).to eq use_case_data.updated_mission
      expect(view_model.sum_currency).to eq use_case_data.sum_currency
    end
  end
end
