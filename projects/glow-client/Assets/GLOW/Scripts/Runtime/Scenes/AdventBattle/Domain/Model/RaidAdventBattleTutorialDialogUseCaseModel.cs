using System.Collections.Generic;
using GLOW.Modules.TutorialTipDialog.Domain.Models;

namespace GLOW.Scenes.AdventBattle.Domain.Model
{
    public record RaidAdventBattleTutorialDialogUseCaseModel(IReadOnlyList<TutorialTipModel> TutorialTipModels)
    {
        public static RaidAdventBattleTutorialDialogUseCaseModel Empty { get; } = 
            new RaidAdventBattleTutorialDialogUseCaseModel(new List<TutorialTipModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}