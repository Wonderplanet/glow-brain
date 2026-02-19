require 'rails_helper'

RSpec.describe "EnhanceResultTranslator" do
  subject { EnhanceResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, before_level: 0, after_level: 1, character_variant: 2, tuning_point: 3, updated_mission: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(EnhanceResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.before_level).to eq use_case_data.before_level
      expect(view_model.after_level).to eq use_case_data.after_level
      expect(view_model.character_variant).to eq use_case_data.character_variant
      expect(view_model.tuning_point).to eq use_case_data.tuning_point
      expect(view_model.updated_mission).to eq use_case_data.updated_mission
    end
  end
end
