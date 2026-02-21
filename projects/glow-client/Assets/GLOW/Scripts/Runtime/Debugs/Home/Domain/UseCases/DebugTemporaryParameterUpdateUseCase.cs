#if GLOW_DEBUG
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Debugs.Home.Domain.Constants;
using GLOW.Debugs.Home.Domain.Models;
using GLOW.Debugs.InGame.Domain.Definitions;
using Zenject;

namespace GLOW.Debugs.Home.Domain.UseCases
{
    public class DebugUpdateSummonTemporaryParameterUseCase
    {
        [Inject] IMstAutoPlayerSequenceRepository MstAutoPlayerSequenceRepository { get; }
        [Inject] IInGameDebugSettingRepository DebugSettingRepository { get; }
        public void UpdateTemporaryParameters(DebugSummonTemporaryParameterModel model)
        {
            var index = DebugSettingRepository.Get().OverrideSummonParameters
                .FindIndex(x => x.Id == model.Id);
            var mstDummyEnemyStageModel = DebugSettingRepository.Get().OverrideSummonParameters[index];
            var parameterModels = DebugSettingRepository.Get().OverrideSummonParameters
                .ReplaceAt(index, CreateDummyMstEnemyStageParameterModel(mstDummyEnemyStageModel, model));

            var debugSettingModel = DebugSettingRepository.Get();
            var debugAssetKeys = debugSettingModel.OverrideUnitAssetKeys
                .ReplaceAt(index, model.AssetKey);
            var updateDebugSettingModel = debugSettingModel with
            {
                OverrideSummonParameters = parameterModels,
                OverrideUnitAssetKeys = debugAssetKeys
            };
            DebugSettingRepository.Save(updateDebugSettingModel);

            MstAutoPlayerSequenceRepository.AddEnemyStageParameterModel(
                CreateDummyMstEnemyStageParameterModel(mstDummyEnemyStageModel, model));

            var debugModels = DebugMstUnitTemporaryParameterDefinitions.DebugAutoPlayerSequenceElementModels;
            MstAutoPlayerSequenceRepository.RefreshSequenceElementModelCache(debugModels);
        }

        MstEnemyStageParameterModel CreateDummyMstEnemyStageParameterModel(MstEnemyStageParameterModel mstEnemyStageParameterModel, DebugSummonTemporaryParameterModel debugSummonTemporaryModel)
        {
            var mstAttackElements = debugSummonTemporaryModel.NormalAttackElements.Select(element => element with
            {
                AttackRange = element.AttackRange with
                {
                    EndPointParameter = debugSummonTemporaryModel.AttackRange
                }
            }).ToList();

            var mstSpecialElements = debugSummonTemporaryModel.SpecialAttackElements.Select(element => element with
            {
                AttackRange = element.AttackRange with
                {
                    EndPointParameter = debugSummonTemporaryModel.AttackRange
                }
            }).ToList();

            var mstAttackBaseData = mstEnemyStageParameterModel.NormalAttack.BaseData;
            mstAttackBaseData = mstAttackBaseData with
            {
                ActionDuration = debugSummonTemporaryModel.NormalAttackActionDuration
            };

            return mstEnemyStageParameterModel with
            {
                AssetKey = debugSummonTemporaryModel.AssetKey,
                NormalAttack = mstEnemyStageParameterModel.NormalAttack with
                {
                    AttackDelay = debugSummonTemporaryModel.NormalAttackDelay,
                    AttackElements = mstAttackElements,
                    BaseData = mstAttackBaseData,
                },
                SpecialAttack = mstEnemyStageParameterModel.SpecialAttack with
                {
                    AttackDelay = debugSummonTemporaryModel.SpecialAttackDelay,
                    AttackElements = mstSpecialElements,
                    BaseData = mstEnemyStageParameterModel.SpecialAttack.BaseData with
                    {
                        ActionDuration = debugSummonTemporaryModel.SpecialActionDuration
                    }
                },
                UnitMoveSpeed = debugSummonTemporaryModel.MoveSpeed,
                AttackComboCycle = debugSummonTemporaryModel.AttackComboCycle,
                WellDistance = new WellDistance(debugSummonTemporaryModel.AttackRange.Value - 0.01f), // 攻撃範囲より少し小さくしておく
            };
        }
    }
}
#endif
