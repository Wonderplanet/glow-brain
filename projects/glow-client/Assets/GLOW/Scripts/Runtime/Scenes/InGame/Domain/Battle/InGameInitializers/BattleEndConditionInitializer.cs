using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class BattleEndConditionInitializer : IBattleEndConditionInitializer
    {
        [Inject] IBattleEndConditionModelFactory BattleEndConditionModelFactory { get; }

        public BattleEndModel Initialize(
            IReadOnlyList<MstStageEndConditionModel> mstStageEndConditionModels,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels,
            InGameType inGameType,
            MstQuestModel mstQuest,
            MasterDataId mstDefenseTargetId)
        {
            var battleEndConditions = mstStageEndConditionModels
                .Select(BattleEndConditionModelFactory.Create)
                .ToList();

            if (inGameType == InGameType.AdventBattle)
            {
                var finishBattleEndConditions =
                    GetFinishBattleEndConditions(new []
                    {
                        // 時間切れ、プレイヤーゲート破壊でFinish
                        StageEndConditionType.TimeOver,
                        StageEndConditionType.PlayerOutpostBreakDown
                    });
                battleEndConditions.AddRange(finishBattleEndConditions);
                return new BattleEndModel(battleEndConditions);
            }

            if (inGameType == InGameType.Pvp)
            {
                var finishBattleEndConditions =
                    GetFinishBattleEndConditions(new []
                    {
                        // 時間切れ、プレイヤーゲート、敵ゲート破壊、ギブアップでFinish
                        StageEndConditionType.TimeOver,
                        StageEndConditionType.PlayerOutpostBreakDown,
                        StageEndConditionType.EnemyOutpostBreakDown,
                        StageEndConditionType.GiveUp
                    });
                battleEndConditions.AddRange(finishBattleEndConditions);
                return new BattleEndModel(battleEndConditions);
            }

            // ステージ終了条件の設定がなければディフォルトの条件をセット
            if (battleEndConditions.Count == 0)
            {
                switch (mstQuest.QuestType)
                {
                    case QuestType.Enhance:
                    {
                        var finishBattleEndConditions =
                            GetFinishBattleEndConditions(new[]
                            {
                                // 時間切れ、プレイヤーゲート破壊でFinish
                                StageEndConditionType.TimeOver,
                                StageEndConditionType.PlayerOutpostBreakDown,
                            });
                        battleEndConditions.AddRange(finishBattleEndConditions);
                        break;
                    }
                    case QuestType.Event:
                    {
                        var stageBattleEndConditions =
                            GetNormalQuestStageEndConditions(mstDefenseTargetId);

                        battleEndConditions.AddRange(stageBattleEndConditions);
                        break;
                    }
                    default:
                    {
                        var stageBattleEndConditions =
                            GetNormalQuestStageEndConditions(mstDefenseTargetId);

                        battleEndConditions.AddRange(stageBattleEndConditions);
                        break;
                    }
                }
            }

            return new BattleEndModel(battleEndConditions);
        }

        IReadOnlyList<IBattleEndConditionModel> GetNormalQuestStageEndConditions(
            MasterDataId mstDefenseTargetId)
        {
            var stageEndConditions = new List<IBattleEndConditionModel>();
            // 敵ゲート破壊で勝利
            stageEndConditions.Add(BattleEndConditionModelFactory.Create(
                StageEndType.Victory,
                StageEndConditionType.EnemyOutpostBreakDown,
                BattleEndConditionValue.Empty,
                BattleEndConditionValue.Empty));

            // プレイヤーゲート破壊で敗北
            stageEndConditions.Add(BattleEndConditionModelFactory.Create(
                StageEndType.Defeat,
                StageEndConditionType.PlayerOutpostBreakDown,
                BattleEndConditionValue.Empty,
                BattleEndConditionValue.Empty));

            // 防衛オブジェクト破壊で敗北
            if (!mstDefenseTargetId.IsEmpty())
            {
                stageEndConditions.Add(BattleEndConditionModelFactory.Create(
                    StageEndType.Defeat,
                    StageEndConditionType.DefenseTargetBreakDown,
                    BattleEndConditionValue.Empty,
                    BattleEndConditionValue.Empty));
            }

            return stageEndConditions;
        }

        IReadOnlyList<IBattleEndConditionModel> GetFinishBattleEndConditions(
            IReadOnlyList<StageEndConditionType> stageEndConditionTypes)
        {
            var stageEndConditions = new List<IBattleEndConditionModel>();

            foreach (var stageEndConditionType in stageEndConditionTypes)
            {
                stageEndConditions.Add(BattleEndConditionModelFactory.Create(
                    StageEndType.Finish,
                    stageEndConditionType,
                    BattleEndConditionValue.Empty,
                    BattleEndConditionValue.Empty));
            }

            return stageEndConditions;
        }
    }
}
