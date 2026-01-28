class TerminalMoveCodeTranslator
  def self.translate(terminal_move_code_model)
    view_model = TerminalMoveCodeViewModel.new
    view_model.terminal_move_id = terminal_move_code_model.terminal_move_id
    view_model.terminal_move_password = terminal_move_code_model.terminal_move_password
    view_model
  end
end
