require 'rails_helper'

RSpec.describe "PuzzleContinueResultTranslator" do
  subject { PuzzleContinueResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, crystal: 0)}

    it do
      view_model = subject
      expect(view_model.is_a?(PuzzleContinueResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.crystal).to eq use_case_data.crystal
    end
  end
end
