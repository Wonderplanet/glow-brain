require 'rails_helper'

RSpec.describe "MstCharacterLevelTranslator" do
  subject { MstCharacterLevelTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, level: 1, required_exp_point: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstCharacterLevelViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.level).to eq use_case_data.level
      expect(view_model.required_exp_point).to eq use_case_data.required_exp_point
    end
  end
end
