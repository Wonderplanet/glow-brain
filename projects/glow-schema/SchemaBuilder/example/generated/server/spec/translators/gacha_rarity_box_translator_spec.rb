require 'rails_helper'

RSpec.describe "GachaRarityBoxTranslator" do
  subject { GachaRarityBoxTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, gacha_prizes: 0, percentage: 1, rarity_type: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(GachaRarityBoxViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.gacha_prizes).to eq use_case_data.gacha_prizes
      expect(view_model.percentage).to eq use_case_data.percentage
      expect(view_model.rarity_type).to eq use_case_data.rarity_type
    end
  end
end
