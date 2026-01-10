require 'rails_helper'

RSpec.describe "MstSubscriptionPassTranslator" do
  subject { MstSubscriptionPassTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, parent_mst_subscription_pass_id: 1, opr_in_app_product_id: 2, day_period: 3, max_subscription_day: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstSubscriptionPassViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.parent_mst_subscription_pass_id).to eq use_case_data.parent_mst_subscription_pass_id
      expect(view_model.opr_in_app_product_id).to eq use_case_data.opr_in_app_product_id
      expect(view_model.day_period).to eq use_case_data.day_period
      expect(view_model.max_subscription_day).to eq use_case_data.max_subscription_day
    end
  end
end
