require 'rails_helper'

RSpec.describe "MstSubscriptionPassRewardTranslator" do
  subject { MstSubscriptionPassRewardTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_subscription_pass_id: 1, day: 2, prize: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstSubscriptionPassRewardViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_subscription_pass_id).to eq use_case_data.mst_subscription_pass_id
      expect(view_model.day).to eq use_case_data.day
      expect(view_model.prize).to eq use_case_data.prize
    end
  end
end
