require 'rails_helper'

RSpec.describe "PuzzleResultCharacterVariantTranslator" do
  subject { PuzzleResultCharacterVariantTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, character_variant_id: 0, exp_point: 1, level: 2, obtained_exp: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(PuzzleResultCharacterVariantViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.character_variant_id).to eq use_case_data.character_variant_id
      expect(view_model.exp_point).to eq use_case_data.exp_point
      expect(view_model.level).to eq use_case_data.level
      expect(view_model.obtained_exp).to eq use_case_data.obtained_exp
    end
  end
end
