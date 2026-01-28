require 'rails_helper'

RSpec.describe "LoginBonusTranslator" do
  subject { LoginBonusTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_login_bonus_id: 0, current_day: 1, received_at: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(LoginBonusViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_login_bonus_id).to eq use_case_data.opr_login_bonus_id
      expect(view_model.current_day).to eq use_case_data.current_day
      expect(view_model.received_at).to eq use_case_data.received_at
    end
  end
end
