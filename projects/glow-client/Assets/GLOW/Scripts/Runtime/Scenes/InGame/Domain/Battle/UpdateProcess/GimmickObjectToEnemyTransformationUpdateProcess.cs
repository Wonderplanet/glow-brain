using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class GimmickObjectToEnemyTransformationUpdateProcess : IGimmickObjectToEnemyTransformationUpdateProcess
    {
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }

        public GimmickObjectToEnemyTransformationUpdateProcessResult UpdateTransformation(
            IReadOnlyList<InGameGimmickObjectModel> gimmickObjectModels,
            IReadOnlyList<GimmickObjectToEnemyTransformationModel> gimmickObjectToEnemyTransformationModels,
            UnitSummonQueueModel unitSummonQueue,
            BossSummonQueueModel bossSummonQueue,
            TickCount tickCount)
        {
            var updatedGimmickObjectModels = gimmickObjectModels;
            var transformEffectStartGimmickObjectModels = new List<InGameGimmickObjectModel>();
            var updatedGimmickObjectToEnemyTransformationModels = new List<GimmickObjectToEnemyTransformationModel>();
            var updatedUnitSummonQueue = unitSummonQueue;
            var updatedBossSummonQueue = bossSummonQueue;

            foreach (var transformationModel in gimmickObjectToEnemyTransformationModels)
            {
                var gimmickObjectModel = gimmickObjectModels.FirstOrDefault(
                    model => model.AutoPlayerSequenceElementId == transformationModel.TransformTargetGimmickSequenceElementId,
                    InGameGimmickObjectModel.Empty);

                // 該当のギミックオブジェクトがなければ何もせずそのまま敵変換キューを廃棄
                if (gimmickObjectModel.IsEmpty())
                {
                    continue;
                }

                // 順序3.敵変換時の演出表示から実際の入れ替え開始まで遅延があるためここで判定している
                // 入れ替えまでの遅延時間がなくなればギミックに入れ替わる形で敵キャラ召喚し敵変換キューを廃棄
                if (transformationModel.RemainingTransformDelay <= TickCount.Zero)
                {
                    // ギミックの現在の座標に置き換え
                    var gimmickPosUnitGenerationModel = transformationModel.UnitGenerationModel with
                    {
                        SummonPosition = CoordinateConverter.OutpostToFieldCoord(BattleSide.Enemy, gimmickObjectModel.Pos)
                    };

                    // 削除するギミックオブジェクトとして設定
                    updatedGimmickObjectModels = updatedGimmickObjectModels.Replace(
                        gimmickObjectModel,
                        gimmickObjectModel with { IsNeedsRemoval = NeedsRemovalFlag.True });

                    // 敵ユニットの召喚キューを追加
                    var mstEnemyStageParameterModel = MstEnemyCharacterDataRepository.GetEnemyStageParameter(transformationModel.EnemyId);
                    if (mstEnemyStageParameterModel.IsBoss)
                    {
                        var bossQueueElement = new BossSummonQueueElement(mstEnemyStageParameterModel.Id, gimmickPosUnitGenerationModel);
                        updatedBossSummonQueue = updatedBossSummonQueue with
                        {
                            SummonQueue = updatedBossSummonQueue.SummonQueue.ToList().ChainAdd(bossQueueElement)
                        };
                    }
                    else
                    {
                        var enemyQueueElement = new UnitSummonQueueElement(transformationModel.EnemyId, gimmickPosUnitGenerationModel);
                        updatedUnitSummonQueue = updatedUnitSummonQueue with
                        {
                            SummonQueue = updatedUnitSummonQueue.SummonQueue.ToList().ChainAdd(enemyQueueElement)
                        };
                    }

                    continue;
                }

                // 順序1.敵変換開始時の演出表示開始。該当ギミックを設定
                if (!transformationModel.IsTransformationStarted)
                {
                    transformEffectStartGimmickObjectModels.Add(gimmickObjectModel);
                }

                // 順序2.敵変換キューの更新
                var updatedTransformationModel = transformationModel with
                {
                    RemainingTransformDelay = transformationModel.RemainingTransformDelay - tickCount,
                    IsTransformationStarted = GimmickObjectTransformationStartedFlag.True
                };

                updatedGimmickObjectToEnemyTransformationModels.Add(updatedTransformationModel);
            }

            return new GimmickObjectToEnemyTransformationUpdateProcessResult(
                updatedGimmickObjectModels,
                transformEffectStartGimmickObjectModels,
                updatedGimmickObjectToEnemyTransformationModels,
                updatedUnitSummonQueue,
                updatedBossSummonQueue);
        }
    }
}
