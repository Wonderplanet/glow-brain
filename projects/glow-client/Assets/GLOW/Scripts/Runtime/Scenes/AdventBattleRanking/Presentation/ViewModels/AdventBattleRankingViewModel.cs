using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
namespace GLOW.Scenes.AdventBattleRanking.Presentation.ViewModels
{
    public record AdventBattleRankingViewModel(AdventBattleRankingElementViewModel CurrentRanking)
    {
        public static AdventBattleRankingViewModel Empty { get; } = new (
            AdventBattleRankingElementViewModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
