require 'rails_helper'

RSpec.describe "UserTranslator" do
  subject { UserTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, nickname: 1, scenario_name: 2, gender: 3, rank_level: 4, user_exp: 5, main_musical_unit_id: 6, favorite_character_variant: 7, support_character_variant: 8, crystal: 9, sum_currency: 10, my_id: 11, user_ap: 12, birth_month: 13, birth_day: 14, created_at: 15, total_login_days: 16, description: 17, local_notification_enabled: 18)}

    it do
      view_model = subject
      expect(view_model.is_a?(UserViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.nickname).to eq use_case_data.nickname
      expect(view_model.scenario_name).to eq use_case_data.scenario_name
      expect(view_model.gender).to eq use_case_data.gender
      expect(view_model.rank_level).to eq use_case_data.rank_level
      expect(view_model.user_exp).to eq use_case_data.user_exp
      expect(view_model.main_musical_unit_id).to eq use_case_data.main_musical_unit_id
      expect(view_model.favorite_character_variant).to eq use_case_data.favorite_character_variant
      expect(view_model.support_character_variant).to eq use_case_data.support_character_variant
      expect(view_model.crystal).to eq use_case_data.crystal
      expect(view_model.sum_currency).to eq use_case_data.sum_currency
      expect(view_model.my_id).to eq use_case_data.my_id
      expect(view_model.user_ap).to eq use_case_data.user_ap
      expect(view_model.birth_month).to eq use_case_data.birth_month
      expect(view_model.birth_day).to eq use_case_data.birth_day
      expect(view_model.created_at).to eq use_case_data.created_at
      expect(view_model.total_login_days).to eq use_case_data.total_login_days
      expect(view_model.description).to eq use_case_data.description
      expect(view_model.local_notification_enabled).to eq use_case_data.local_notification_enabled
    end
  end
end
