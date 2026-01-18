using System.Collections.Generic;
using GLOW.Scenes.QuestContentTop.Domain.enums;

namespace GLOW.Scenes.QuestContentTop.Domain.UseCaseModel
{
    public record QuestContentTopSectionUseCaseModel(
        QuestContentTopSectionType Type,
        IReadOnlyList<QuestContentTopElementUseCaseModel> Items
    )
    {
        public static QuestContentTopSectionUseCaseModel Empty { get; } = new(
            QuestContentTopSectionType.Other,
            new List<QuestContentTopElementUseCaseModel>()
        );

        public bool IsEmpty => ReferenceEquals(this, Empty);
    };
}
