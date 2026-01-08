using System.Collections.Generic;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Constants;
#if GLOW_INGAME_DEBUG
using GLOW.Debugs.Home.Domain.Constants;
using GLOW.Debugs.InGame.Domain.Definitions;
#endif
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.InGame.Domain.Battle.Calculator;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class DeckInitializer : IDeckInitializer
    {
        [Inject] IPvpSelectedOpponentStatusCacheRepository OpponentStatusCacheRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] ISpecialRoleSpecialAttackFactory SpecialRoleSpecialAttackFactory { get; }
        [Inject] IUnitSummonCoolTimeCalculator UnitSummonCoolTimeCalculator { get; }
#if GLOW_INGAME_DEBUG
        [Inject] IInGameDebugSettingRepository DebugSettingRepository { get; }
#endif

        public DeckInitializerResult Initialize(
            OutpostEnhancementModel outpostEnhancementModel,
            OutpostEnhancementModel pvpOpponentOutpostEnhancementModel)
        {
            var deckUnits = InitializeDeckUnits(outpostEnhancementModel);
            var pvpOpponentDeckUnits = InitializePvpOpponentDeckUnits(pvpOpponentOutpostEnhancementModel);

            return new DeckInitializerResult(
                deckUnits,
                pvpOpponentDeckUnits);
        }

        List<DeckUnitModel> InitializeDeckUnits(OutpostEnhancementModel outpostEnhancementModel)
        {
            var deckUnits = new List<DeckUnitModel>();

#if GLOW_INGAME_PROFILE
            deckUnits.Add(CreateDeckUnitModel(new MasterDataId("test_11")));
            deckUnits.Add(CreateDeckUnitModel(new MasterDataId("test_12")));
            deckUnits.Add(CreateDeckUnitModel(new MasterDataId("test_13")));
            deckUnits.Add(CreateDeckUnitModel(new MasterDataId("test_14")));
            deckUnits.Add(CreateDeckUnitModel(new MasterDataId("test_15")));
            deckUnits.Add(CreateDeckUnitModel(new MasterDataId("test_16")));
            deckUnits.Add(CreateDeckUnitModel(new MasterDataId("test_17")));
            deckUnits.Add(CreateDeckUnitModel(new MasterDataId("test_18")));
            deckUnits.Add(CreateDeckUnitModel(new MasterDataId("test_19")));
            deckUnits.Add(CreateDeckUnitModel(new MasterDataId("test_20")));
#else
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            if (tutorialStatus.IsIntroduction())
            {
                deckUnits = CreateTutorialDeckUnitModels(outpostEnhancementModel);
            }
            else
            {
                deckUnits = CreateDeckUnitModels(outpostEnhancementModel);
            }

#endif
            return deckUnits;
        }

        List<DeckUnitModel> CreateDeckUnitModels(OutpostEnhancementModel outpostEnhancement)
        {
            var deckUnits = new List<DeckUnitModel>();

            var fetchOther = GameRepository.GetGameFetchOther();
            var selectedParty = PartyCacheRepository.GetCurrentPartyModel().GetUnitList();

#if GLOW_INGAME_DEBUG
            if (DebugSettingRepository.Get().IsOverrideUnits)
            {
                for (var i = 0; i < PartyMemberSlotCount.Max.Value; i++)
                {
                    if (DebugMstUnitTemporaryParameterDefinitions.DebugUserUnitModels.Count > i)
                    {
                        deckUnits.Add(CreateDeckUnitModel(DebugMstUnitTemporaryParameterDefinitions.DebugUserUnitModels[i], outpostEnhancement));
                    }
                    else
                    {
                        var noLockModel =  DeckUnitModel.Empty with
                        {
                            IsDeckComponentLock = IsDeckComponentLock.False
                        };
                        deckUnits.Add(noLockModel);
                    }
                }

                return deckUnits;
            }
#endif

            for (var i = 0; i < PartyMemberSlotCount.Max.Value; i++)
            {
                if(i >= selectedParty.Count)
                {
                    var lockedModel =  DeckUnitModel.Empty with
                    {
                        IsDeckComponentLock = IsDeckComponentLock.True
                    };
                    deckUnits.Add(lockedModel);
                }
                else
                {
                    var userUnit = fetchOther.UserUnitModels
                        .Find(unit => unit.UsrUnitId == selectedParty[i]);

                    if (null == userUnit)
                    {
                        var noLockModel =  DeckUnitModel.Empty with
                        {
                            IsDeckComponentLock = IsDeckComponentLock.False
                        };
                        deckUnits.Add(noLockModel);
                    }
                    else deckUnits.Add(CreateDeckUnitModel(userUnit, outpostEnhancement));
                }
            }

            return deckUnits;
        }

        DeckUnitModel CreateDeckUnitModel(
            UserUnitModel userUnitModel,
            OutpostEnhancementModel outpostEnhancement)
        {
            MstCharacterModel mstCharacter = MstCharacterDataRepository.GetCharacter(userUnitModel.MstUnitId);

            var summonCoolTime = UnitSummonCoolTimeCalculator.Calculate(mstCharacter, outpostEnhancement);
            var specialAttackData = SpecialRoleSpecialAttackFactory.CreateSpecialRoleSpecialAttack(mstCharacter, userUnitModel);

            return new DeckUnitModel(
                BattleSide.Player,
                IsDeckComponentLock.False,
                userUnitModel.UsrUnitId,
                mstCharacter.Id,
                mstCharacter.AssetKey,
                mstCharacter.RoleType,
                mstCharacter.Color,
                mstCharacter.Rarity,
                userUnitModel.Grade,
                mstCharacter.SummonCost,
                summonCoolTime,
                TickCount.Zero,
                false,
                specialAttackData,
                mstCharacter.SpecialAttackInitialCoolTime,
                mstCharacter.SpecialAttackCoolTime,
                mstCharacter.SpecialAttackInitialCoolTime,
                mstCharacter.SpecialAttackInitialCoolTime,
                false);
        }

        #region チュートリアル用

        List<DeckUnitModel> CreateTutorialDeckUnitModels(OutpostEnhancementModel outpostEnhancement)
        {
            var deckUnits = new List<DeckUnitModel>();

            var tutorialUnits = CreateTutorialUserUnitModels();

            deckUnits.Add(CreateDeckUnitModel(tutorialUnits[0], outpostEnhancement));
            deckUnits.Add(CreateDeckUnitModel(tutorialUnits[1], outpostEnhancement));
            deckUnits.Add(CreateDeckUnitModel(tutorialUnits[2], outpostEnhancement));
            deckUnits.Add(CreateDeckUnitModel(tutorialUnits[3], outpostEnhancement));
            deckUnits.Add(CreateDeckUnitModel(tutorialUnits[4], outpostEnhancement));

            deckUnits.Add(DeckUnitModel.Empty with { IsDeckComponentLock = IsDeckComponentLock.True});
            deckUnits.Add(DeckUnitModel.Empty with { IsDeckComponentLock = IsDeckComponentLock.True});
            deckUnits.Add(DeckUnitModel.Empty with { IsDeckComponentLock = IsDeckComponentLock.True});
            deckUnits.Add(DeckUnitModel.Empty with { IsDeckComponentLock = IsDeckComponentLock.True});
            deckUnits.Add(DeckUnitModel.Empty with { IsDeckComponentLock = IsDeckComponentLock.True});

            return deckUnits;
        }

        List<UserUnitModel> CreateTutorialUserUnitModels()
        {
            var baseUnitModel = new UserUnitModel(
                MasterDataId.Empty,
                UserDataId.Empty,
                new UnitLevel(80),
                new UnitRank(5),
                new UnitGrade(5),
                NewEncyclopediaFlag.False);

            var list = new List<UserUnitModel>();

            foreach (var unitId in TutorialDefinitionIds.TutorialUnitIds)
            {
                var userUnitModel = baseUnitModel with
                {
                    MstUnitId = unitId.MstUnitId,
                    UsrUnitId = unitId.UserUnitId,
                };
                list.Add(userUnitModel);
            }

            return list;
        }
        #endregion

        #region Pvp対戦相手用のDeck

        List<DeckUnitModel> InitializePvpOpponentDeckUnits(OutpostEnhancementModel outpostEnhancement)
        {
            var opponentStatus = OpponentStatusCacheRepository.GetOpponentStatus();
            if (opponentStatus.IsEmpty())
            {
                // 主にPvpでない場合ここに
                return new List<DeckUnitModel>();
            }

            // プレイヤー側と違いEmptyでIsDeckComponentLock.Trueに設定したDeckUnitの追加は不要のはず
            var deckUnits = new List<DeckUnitModel>();
            foreach (var unit in opponentStatus.PvpUnits)
            {
                var deckUnit = CreateDeckUnitModel(unit, outpostEnhancement);
                deckUnits.Add(deckUnit);
            }

            return deckUnits;
        }

        DeckUnitModel CreateDeckUnitModel(
            PvpUnitModel pvpUnitModel,
            OutpostEnhancementModel outpostEnhancementModel)
        {
            if (pvpUnitModel.IsEmpty())
            {
                // 念の為
                return DeckUnitModel.Empty with
                {
                    IsDeckComponentLock = IsDeckComponentLock.True
                };
            }

            MstCharacterModel mstCharacter = MstCharacterDataRepository.GetCharacter(pvpUnitModel.MstUnitId);

            var summonCoolTime = UnitSummonCoolTimeCalculator.Calculate(mstCharacter, outpostEnhancementModel);
            var specialAttackData =
                SpecialRoleSpecialAttackFactory.CreateSpecialRoleSpecialAttack(
                    mstCharacter,
                    pvpUnitModel.UnitGrade,
                    pvpUnitModel.UnitLevel);

            return new DeckUnitModel(
                BattleSide.Enemy,
                IsDeckComponentLock.False,
                UserDataId.Empty,
                mstCharacter.Id,
                mstCharacter.AssetKey,
                mstCharacter.RoleType,
                mstCharacter.Color,
                mstCharacter.Rarity,
                pvpUnitModel.UnitGrade,
                mstCharacter.SummonCost,
                summonCoolTime,
                TickCount.Zero,
                false,
                specialAttackData,
                mstCharacter.SpecialAttackInitialCoolTime,
                mstCharacter.SpecialAttackCoolTime,
                mstCharacter.SpecialAttackInitialCoolTime,
                mstCharacter.SpecialAttackInitialCoolTime,
                false);
        }
        #endregion
    }
}
