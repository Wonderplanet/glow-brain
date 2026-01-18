using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.AdventBattle.Presentation.View
{
    public interface IAdventBattleTopViewDelegate
    {
        void OnViewDidLoad();
        void OnViewWillAppear();

        void OnHelpButtonTapped();
        void OnEnemyDetailButtonTapped();
        void OnSpecialRuleButtonTapped();

        void OnRankingButtonTapped();
        void OnRewardListButtonTapped();

        void OnMissionButtonTapped();

        void OnBonusUnitButtonTapped();
        void OnPartyFormationButtonTapped();

        void OnBattleStartButtonTapped();

        void OnBackButtonTapped();
        void OnRewardIconSelected(PlayerResourceIconViewModel viewModel);
        void OnEscape();
    }
}
