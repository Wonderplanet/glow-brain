#if GLOW_DEBUG
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Debugs.Home.Domain.Constants;
using GLOW.Debugs.Home.Domain.Models;
using Zenject;

namespace GLOW.Debugs.Home.Domain.UseCases
{
    public class DebugSetMstUnitTemporaryParameterUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IGameRepository GameRepository { get; }

        public void SetDebugUnitTemporaries(DebugMstUnitTemporaryParameterModel model)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userUnitList = gameFetchOther.UserUnitModels.ToList();

            // 重複を消す
            var targetIds = DebugMstUnitTemporaryParameterDefinitions.DebugUserUnitModels
                .Select(m => m.UsrUnitId).ToHashSet();
            var modelsToKeep = userUnitList.Where(m => !targetIds.Contains(m.UsrUnitId)).ToList();
            userUnitList = modelsToKeep.Concat(DebugMstUnitTemporaryParameterDefinitions.DebugUserUnitModels).ToList();
            var newFetchOtherModel = gameFetchOther with
            {
                UserUnitModels = userUnitList
            };

            GameManagement.SaveGameFetchOther(newFetchOtherModel);

            var mstCharacterModel = CreateDummyCharacterModel(model);

            // デバッグ用のキャラクターは残しておく
            var mstCharacterModels = MstCharacterDataRepository.GetCharacters();
            var dummyIds = DebugMstUnitTemporaryParameterDefinitions.DebugDummyIds;

            // 更新するデバッグ用キャラを差し替えまたは追加する
            var debugCharacterModels = mstCharacterModels
                .Where(m => dummyIds.Contains(m.Id))
                .ToList();

            debugCharacterModels = debugCharacterModels
                .ReplaceOrAdd(m => m.Id == mstCharacterModel.Id, mstCharacterModel)
                .ToList();

            // まだ含まれていないデバッグ用キャラがいない場合はここで追加しておく
            var dummyMstCharacterModels = DebugMstUnitTemporaryParameterDefinitions.DebugMstCharacterDummyTemplates;
            foreach (var dummyMstCharacterModel in dummyMstCharacterModels)
            {
                if (debugCharacterModels.Any(model => model.Id == dummyMstCharacterModel.Id)) continue;
                debugCharacterModels.Add(dummyMstCharacterModel);
            }

            MstCharacterDataRepository.RefreshCharacterModelCache(debugCharacterModels);
        }

        MstCharacterModel CreateDummyCharacterModel(DebugMstUnitTemporaryParameterModel model)
        {
            var mstDummyCharacterModel = DebugMstUnitTemporaryParameterDefinitions.DebugMstCharacterDummyTemplates
                .FirstOrDefault(x => x.Id == model.Id);

            return CreateDummyMstCharacterModel(mstDummyCharacterModel, model);
        }

        MstCharacterModel CreateDummyMstCharacterModel(MstCharacterModel mstCharacterModel, DebugMstUnitTemporaryParameterModel debugMstUnitModel)
        {
            var mstAttackElements = debugMstUnitModel.NormalAttackElements
                .Select(element => element with
            {
                AttackRange = element.AttackRange with
                {
                    EndPointParameter = debugMstUnitModel.AttackRange
                }
            }).ToList();

            var mstAttackBaseData = mstCharacterModel.NormalMstAttackModel.AttackData.BaseData;
            mstAttackBaseData = mstAttackBaseData with
            {
                ActionDuration = debugMstUnitModel.NormalAttackActionDuration
            };

            var mstSpecialAttackBaseData = mstCharacterModel.SpecialAttacks[0].AttackData.BaseData;
            mstSpecialAttackBaseData = mstSpecialAttackBaseData with
            {
                ActionDuration = debugMstUnitModel.SpecialActionDuration
            };

            var mstSpecialAttackElements = debugMstUnitModel.SpecialAttackElements
                .Select(element => element with
            {
                AttackRange = element.AttackRange with
                {
                    EndPointParameter = debugMstUnitModel.AttackRange
                }
            }).ToList();

            return mstCharacterModel with
            {
                AssetKey = debugMstUnitModel.AssetKey,
                UnitMoveSpeed = debugMstUnitModel.UnitMoveSpeed,
                WellDistance = new WellDistance(debugMstUnitModel.AttackRange.Value - 0.01f),// 攻撃範囲より少し小さくしておく
                NormalMstAttackModel = mstCharacterModel.NormalMstAttackModel with
                {
                    AttackData = mstCharacterModel.NormalMstAttackModel.AttackData with
                    {
                        AttackDelay = debugMstUnitModel.NormalAttackDelay,
                        AttackElements = mstAttackElements,
                        BaseData = mstAttackBaseData,
                    }
                },
                SpecialAttacks = new List<MstSpecialAttackModel>
                {
                    mstCharacterModel.SpecialAttacks[0] with
                    {
                        AttackData = mstCharacterModel.SpecialAttacks[0].AttackData with
                        {
                            AttackDelay = debugMstUnitModel.SpecialAttackDelay,
                            AttackElements = mstSpecialAttackElements,
                            BaseData = mstSpecialAttackBaseData,
                        }
                    }
                }
            };
        }
    }
}
#endif
