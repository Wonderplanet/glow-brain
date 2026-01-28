using System.Collections.Generic;


namespace GLOW.Scenes.QuestSelect.Presentation
{
    public record QuestSelectViewModel(CollectionViewCurrentIndex CurrentIndex, IReadOnlyList<QuestSelectContentViewModel> Items);

    public record CollectionViewCurrentIndex(int Value);
}
