require 'rails_helper'

RSpec.describe "GachaResultTranslator" do
  subject { GachaResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, received: 0, received_histories: 1, gacha: 2, gacha_sale_history: 3, gacha_oha_history: 4, updated_mission: 5, updated_solo_stories: 6)}

    it do
      view_model = subject
      expect(view_model.is_a?(GachaResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.received).to eq use_case_data.received
      expect(view_model.received_histories).to eq use_case_data.received_histories
      expect(view_model.gacha).to eq use_case_data.gacha
      expect(view_model.gacha_sale_history).to eq use_case_data.gacha_sale_history
      expect(view_model.gacha_oha_history).to eq use_case_data.gacha_oha_history
      expect(view_model.updated_mission).to eq use_case_data.updated_mission
      expect(view_model.updated_solo_stories).to eq use_case_data.updated_solo_stories
    end
  end
end
