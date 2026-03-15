require 'rails_helper'

RSpec.describe "DebugActionTranslator" do
  subject { DebugActionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, category: 0, title: 1, description: 2, action_name: 3, first_param_name: 4, second_param_name: 5, third_param_name: 6)}

    it do
      view_model = subject
      expect(view_model.is_a?(DebugActionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.category).to eq use_case_data.category
      expect(view_model.title).to eq use_case_data.title
      expect(view_model.description).to eq use_case_data.description
      expect(view_model.action_name).to eq use_case_data.action_name
      expect(view_model.first_param_name).to eq use_case_data.first_param_name
      expect(view_model.second_param_name).to eq use_case_data.second_param_name
      expect(view_model.third_param_name).to eq use_case_data.third_param_name
    end
  end
end
