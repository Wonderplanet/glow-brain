using System.Collections.Generic;
using System.Linq;

namespace GLOW.Scenes.QuestContentTop.Presentation.ViewModel
{
    public record QuestContentTopViewModel(IReadOnlyList<QuestContentTopSectionViewModel> SectionViewModels)
    {
        public IReadOnlyList<QuestContentCellViewModel> CreateAllItems()
        {
            return SectionViewModels.SelectMany(section => section.Items).ToList();
        }
    };
}
