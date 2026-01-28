using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.TutorialTipDialog.Domain.Definitions;
using GLOW.Scenes.EventQuestSelect.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public class AdventBattleOpenStatusEvaluator : IAdventBattleOpenStatusEvaluator
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public AdventBattleOpenStatus Evaluate(MstAdventBattleModel targetMstAdventBattleModel)
        {
            var userLevel = GameRepository.GetGameFetch().UserParameterModel.Level;
            return IsOpenAdventBattle(targetMstAdventBattleModel, userLevel);
        }

        AdventBattleOpenStatus IsOpenAdventBattle(MstAdventBattleModel model, UserLevel userLevel)
        {
            if(model.IsEmpty()) return new AdventBattleOpenStatus(AdventBattleOpenStatusType.ClosedEmpty);
            if (!IsReleaseRank(userLevel)) return new AdventBattleOpenStatus(AdventBattleOpenStatusType.RankLocked);
            if(TimeProvider.Now < model.StartDateTime.Value) return new AdventBattleOpenStatus(AdventBattleOpenStatusType.BeforeOpened);
            if(model.EndDateTime.Value < TimeProvider.Now) return new AdventBattleOpenStatus(AdventBattleOpenStatusType.Closed);
            return new AdventBattleOpenStatus(AdventBattleOpenStatusType.Opened);
        }

        bool IsReleaseRank(UserLevel userLevel)
        {
            var releaseRank = MstTutorialRepository.GetMstTutorialModels()
                .FirstOrDefault(m => m.TutorialFunctionName == HelpDialogIdDefinitions.AdventBattle,
                    MstTutorialModel.Empty);
            if (releaseRank.IsEmpty())
            {
                return true;
            }
            return releaseRank.ConditionValue.ToUserLevel() <= userLevel;
        }

    }
}