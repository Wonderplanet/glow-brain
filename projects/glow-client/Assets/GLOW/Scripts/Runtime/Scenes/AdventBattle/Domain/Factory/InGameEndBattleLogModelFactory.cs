using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.LogModel;
using GLOW.Scenes.InGame.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Domain.Factory
{
    public class InGameEndBattleLogModelFactory : IInGameEndBattleLogModelFactory
    {
        [Inject] IInGameLogRepository InGameLogRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IPartyStatusModelFactory PartyStatusModelFactory { get; }
        [Inject] IInGameScene InGameScene { get; }

        InGameEndBattleLogModel IInGameEndBattleLogModelFactory.CreateInGameEndBattleLogModel(
            IReadOnlyList<UserUnitModel> userUnitModels,
            StageClearTime stageClearTime)
        {
            var inGameLog = InGameLogRepository.GetLog();

            var partyStatusModels = PartyCacheRepository
                .GetCurrentPartyModel()
                .GetUnitList()
                .Join(userUnitModels, id => id, model => model.UsrUnitId, (_, model) => model)
                .Select(m => PartyStatusModelFactory.CreatePartyStatusModel(
                    m,
                    InGameScene.Type,
                    InGameScene.MstQuest.Id,
                    InGameScene.EventBonusGroupId,
                    InGameScene.SpecialRuleUnitStatusModels))
                .ToList();

            return new InGameEndBattleLogModel(
                inGameLog.DefeatEnemyCount,
                inGameLog.DefeatBossEnemyCount,
                InGameScene.ScoreModel.TotalScore,
                partyStatusModels,
                stageClearTime,
                inGameLog.MaxDamage,
                inGameLog.DiscoveredMstEnemyCharacterIds
            );
        }
    }
}
