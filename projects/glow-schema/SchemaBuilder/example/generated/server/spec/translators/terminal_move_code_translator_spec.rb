require 'rails_helper'

RSpec.describe "TerminalMoveCodeTranslator" do
  subject { TerminalMoveCodeTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, terminal_move_id: 0, terminal_move_password: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(TerminalMoveCodeViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.terminal_move_id).to eq use_case_data.terminal_move_id
      expect(view_model.terminal_move_password).to eq use_case_data.terminal_move_password
    end
  end
end
