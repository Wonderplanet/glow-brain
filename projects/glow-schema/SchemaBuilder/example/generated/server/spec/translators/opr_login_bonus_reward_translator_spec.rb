require 'rails_helper'

RSpec.describe "OprLoginBonusRewardTranslator" do
  subject { OprLoginBonusRewardTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_login_bonus_id: 0, day: 1, prize: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprLoginBonusRewardViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_login_bonus_id).to eq use_case_data.opr_login_bonus_id
      expect(view_model.day).to eq use_case_data.day
      expect(view_model.prize).to eq use_case_data.prize
    end
  end
end
