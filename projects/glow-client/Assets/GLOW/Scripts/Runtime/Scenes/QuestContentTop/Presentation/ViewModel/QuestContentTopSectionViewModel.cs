using System.Collections.Generic;
using GLOW.Scenes.QuestContentTop.Domain.enums;

namespace GLOW.Scenes.QuestContentTop.Presentation.ViewModel
{
    public record QuestContentTopSectionViewModel(
        QuestContentTopSectionType Type,
        IReadOnlyList <QuestContentCellViewModel> Items)
    {
        public static QuestContentTopSectionViewModel Empty { get; } =
            new(QuestContentTopSectionType.Other, new List<QuestContentCellViewModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
