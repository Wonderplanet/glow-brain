using System.Collections.Generic;

namespace GLOW.Scenes.MessageBox.Presentation.ViewModel
{
    public record MessageBoxViewModel(IReadOnlyList<IMessageBoxCellViewModel> MessageBoxCellViewModels);
}
