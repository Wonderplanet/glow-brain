require 'rails_helper'

RSpec.describe "SubscriptionPassTranslator" do
  subject { SubscriptionPassTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_in_app_product_id: 0, current_day: 1, stock: 2, start_at: 3, end_at: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(SubscriptionPassViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_in_app_product_id).to eq use_case_data.opr_in_app_product_id
      expect(view_model.current_day).to eq use_case_data.current_day
      expect(view_model.stock).to eq use_case_data.stock
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
    end
  end
end
