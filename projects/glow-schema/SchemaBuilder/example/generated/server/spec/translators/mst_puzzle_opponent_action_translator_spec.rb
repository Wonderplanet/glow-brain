require 'rails_helper'

RSpec.describe "MstPuzzleOpponentActionTranslator" do
  subject { MstPuzzleOpponentActionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, pattern_id: 1, action_type: 2, filter_type: 3, lower_limit: 4, upper_limit: 5, priority: 6, skill_type: 7, adoption_percentage: 8, min_interval: 9, max_usage_count: 10, duration: 11, power_percentage: 12, bit_target: 13, action_value: 14)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstPuzzleOpponentActionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.pattern_id).to eq use_case_data.pattern_id
      expect(view_model.action_type).to eq use_case_data.action_type
      expect(view_model.filter_type).to eq use_case_data.filter_type
      expect(view_model.lower_limit).to eq use_case_data.lower_limit
      expect(view_model.upper_limit).to eq use_case_data.upper_limit
      expect(view_model.priority).to eq use_case_data.priority
      expect(view_model.skill_type).to eq use_case_data.skill_type
      expect(view_model.adoption_percentage).to eq use_case_data.adoption_percentage
      expect(view_model.min_interval).to eq use_case_data.min_interval
      expect(view_model.max_usage_count).to eq use_case_data.max_usage_count
      expect(view_model.duration).to eq use_case_data.duration
      expect(view_model.power_percentage).to eq use_case_data.power_percentage
      expect(view_model.bit_target).to eq use_case_data.bit_target
      expect(view_model.action_value).to eq use_case_data.action_value
    end
  end
end
