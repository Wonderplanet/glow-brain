require 'rails_helper'

RSpec.describe "OprLoginBonusTranslator" do
  subject { OprLoginBonusTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, login_bonus_category: 1, start_at: 2, end_at: 3, user_created_since: 4, user_created_until: 5, sleep_login_days: 6, asset_key: 7)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprLoginBonusViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.login_bonus_category).to eq use_case_data.login_bonus_category
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
      expect(view_model.user_created_since).to eq use_case_data.user_created_since
      expect(view_model.user_created_until).to eq use_case_data.user_created_until
      expect(view_model.sleep_login_days).to eq use_case_data.sleep_login_days
      expect(view_model.asset_key).to eq use_case_data.asset_key
    end
  end
end
