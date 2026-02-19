require 'rails_helper'

RSpec.describe "MstItemTranslator" do
  subject { MstItemTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, name: 1, asset_key: 2, category: 3, power: 4, description: 5, rarity: 6)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstItemViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.asset_key).to eq use_case_data.asset_key
      expect(view_model.category).to eq use_case_data.category
      expect(view_model.power).to eq use_case_data.power
      expect(view_model.description).to eq use_case_data.description
      expect(view_model.rarity).to eq use_case_data.rarity
    end
  end
end
