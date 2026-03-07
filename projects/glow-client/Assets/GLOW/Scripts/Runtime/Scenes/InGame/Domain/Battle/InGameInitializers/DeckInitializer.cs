using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Constants;
#if GLOW_INGAME_DEBUG
using GLOW.Debugs.Home.Domain.Constants;
using GLOW.Debugs.InGame.Domain.Definitions;
#endif
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.InGame.Domain.Battle.Calculator;
using GLOW.Scenes.InGame.Domain.Battle.Evaluator;
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
        [Inject] IUnitSpecialAttackCoolTimeCalculator SpecialAttackCoolTimeCalculator { get; }
        [Inject] IInGameSpecialRuleUnitStatusProvider InGameSpecialRuleUnitStatusProvider { get; }
        [Inject] IArtworkEffectActivationEvaluator ArtworkEffectActivationEvaluator { get; }
        [Inject] IArtworkEffectTargetEvaluator ArtworkEffectTargetEvaluator { get; }
#if GLOW_INGAME_DEBUG
        [Inject] IInGameDebugSettingRepository DebugSettingRepository { get; }
#endif

        public DeckInitializerResult Initialize(
            OutpostEnhancementModel outpostEnhancementModel,
            OutpostEnhancementModel pvpOpponentOutpostEnhancementModel,
            ArtworkEffectModel artworkEffectModel,
            ArtworkEffectModel pvpOpponentArtworkEffectModel,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var deckUnits = InitializeDeckUnits(
                outpostEnhancementModel,
                artworkEffectModel,
                specialRuleUnitStatusModels);
            var pvpOpponentDeckUnits = InitializePvpOpponentDeckUnits(
                pvpOpponentOutpostEnhancementModel,
                pvpOpponentArtworkEffectModel,
                specialRuleUnitStatusModels);

            return new DeckInitializerResult(
                deckUnits,
                pvpOpponentDeckUnits);
        }

        List<DeckUnitModel> InitializeDeckUnits(
            OutpostEnhancementModel outpostEnhancementModel,
            ArtworkEffectModel artworkEffectModel,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
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
                deckUnits = CreateTutorialDeckUnitModels(
                    outpostEnhancementModel,
                    specialRuleUnitStatusModels);
            }
            else
            {
                deckUnits = CreateDeckUnitModels(
                    outpostEnhancementModel,
                    specialRuleUnitStatusModels,
                    artworkEffectModel);
            }

#endif
            return deckUnits;
        }

        List<DeckUnitModel> CreateDeckUnitModels(
            OutpostEnhancementModel outpostEnhancement,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels,
            ArtworkEffectModel artworkEffectModel)
        {
            var deckUnits = new List<DeckUnitModel>();

            var fetchOther = GameRepository.GetGameFetchOther();
            var selectedParty = PartyCacheRepository.GetCurrentPartyModel().GetUnitList();
            var partyMstCharacterModels = selectedParty
                .Select(usrUnitId => fetchOther.UserUnitModels
                    .FirstOrDefault(userUnit => userUnit.UsrUnitId == usrUnitId))
                .Where(userUnit => userUnit != null)
                .Select(userUnit => MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId))
                .ToList();

            // キャラ毎の原画効果ボーナスを計算
            var artworkEffectSummonCoolTimeBonuses = GetArtworkEffectBonus(
                partyMstCharacterModels,
                artworkEffectModel,
                ArtworkEffectType.ResummonSpeedUp);
            var artworkEffectSpecialAttackCoolTimeBonuses = GetArtworkEffectBonus(
                partyMstCharacterModels,
                artworkEffectModel,
                ArtworkEffectType.SpecialAttackChargeSpeedUp);
            var artworkEffectHpUpBonuses = GetArtworkEffectBonus(
                partyMstCharacterModels,
                artworkEffectModel,
                ArtworkEffectType.HpUp);
            var artworkEffectAttackPowerUpBonuses = GetArtworkEffectBonus(
                partyMstCharacterModels,
                artworkEffectModel,
                ArtworkEffectType.AttackPowerUp);

#if GLOW_INGAME_DEBUG
            if (DebugSettingRepository.Get().IsOverrideUnits)
            {
                for (var i = 0; i < PartyMemberSlotCount.Max.Value; i++)
                {
                    if (DebugMstUnitTemporaryParameterDefinitions.DebugUserUnitModels.Count > i)
                    {
                        deckUnits.Add(CreateDeckUnitModel(
                            DebugMstUnitTemporaryParameterDefinitions.DebugUserUnitModels[i],
                            outpostEnhancement,
                            specialRuleUnitStatusModels,
                            artworkEffectSummonCoolTimeBonuses,
                            artworkEffectSpecialAttackCoolTimeBonuses,
                            artworkEffectHpUpBonuses,
                            artworkEffectAttackPowerUpBonuses));
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
                    else deckUnits.Add(CreateDeckUnitModel(
                        userUnit,
                        outpostEnhancement,
                        specialRuleUnitStatusModels,
                        artworkEffectSummonCoolTimeBonuses,
                        artworkEffectSpecialAttackCoolTimeBonuses,
                        artworkEffectHpUpBonuses,
                        artworkEffectAttackPowerUpBonuses));
                }
            }

            return deckUnits;
        }

        DeckUnitModel CreateDeckUnitModel(
            UserUnitModel userUnitModel,
            OutpostEnhancementModel outpostEnhancement,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels,
            IReadOnlyDictionary<MasterDataId, ArtworkEffectValue> artworkEffectSummonCoolTimeBonuses,
            IReadOnlyDictionary<MasterDataId, ArtworkEffectValue> artworkEffectSpecialAttackCoolTimeBonuses,
            IReadOnlyDictionary<MasterDataId, ArtworkEffectValue> artworkHpUpBonuses,
            IReadOnlyDictionary<MasterDataId, ArtworkEffectValue> artworkAttackPowerUpBonuses)
        {
            MstCharacterModel mstCharacter = MstCharacterDataRepository.GetCharacter(userUnitModel.MstUnitId);
            // 原画効果ボーナス取得
            var artworkSummonBonus = artworkEffectSummonCoolTimeBonuses
                .GetValueOrDefault(mstCharacter.Id, ArtworkEffectValue.Zero)
                .ToTickCount();
            var artworkSpecialAttackBonus = artworkEffectSpecialAttackCoolTimeBonuses
                .GetValueOrDefault(mstCharacter.Id, ArtworkEffectValue.Zero)
                .ToTickCount();
            var artworkHpUpBonus = artworkHpUpBonuses
                .GetValueOrDefault(mstCharacter.Id, ArtworkEffectValue.Zero)
                .ToPercentageM();
            artworkHpUpBonus += PercentageM.Hundred;
            var artworkAttackPowerUpBonus = artworkAttackPowerUpBonuses
                .GetValueOrDefault(mstCharacter.Id, ArtworkEffectValue.Zero)
                .ToPercentageM();
            artworkAttackPowerUpBonus += PercentageM.Hundred;

            var specialRuleUnitStatus = InGameSpecialRuleUnitStatusProvider.GetSpecialRuleUnitStatus(
                mstCharacter,
                specialRuleUnitStatusModels);
            var summonCoolTime = UnitSummonCoolTimeCalculator.Calculate(
                mstCharacter,
                outpostEnhancement,
                specialRuleUnitStatus.SummonCoolTimeParameter,
                artworkSummonBonus);
            var specialAttackData = SpecialRoleSpecialAttackFactory.CreateSpecialRoleSpecialAttack(mstCharacter, userUnitModel);

            var specialAttackInitialCoolTime = SpecialAttackCoolTimeCalculator.Calculate(
                mstCharacter.SpecialAttackInitialCoolTime,
                specialRuleUnitStatus.SpecialAttackCoolTimeParameter,
                artworkSpecialAttackBonus);

            var specialAttackCoolTime = SpecialAttackCoolTimeCalculator.Calculate(
                mstCharacter.SpecialAttackCoolTime,
                specialRuleUnitStatus.SpecialAttackCoolTimeParameter,
                artworkSpecialAttackBonus);

            return new DeckUnitModel(
                BattleSide.Player,
                IsDeckComponentLock.False,
                userUnitModel.UsrUnitId,
                mstCharacter.Id,
                mstCharacter.MstSeriesId,
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
                specialAttackInitialCoolTime,
                specialAttackCoolTime,
                specialAttackInitialCoolTime,
                specialAttackInitialCoolTime,
                artworkHpUpBonus,
                artworkAttackPowerUpBonus,
                artworkSpecialAttackBonus,
                artworkSummonBonus,
                false);
        }

        #region チュートリアル用

        List<DeckUnitModel> CreateTutorialDeckUnitModels(
            OutpostEnhancementModel outpostEnhancement,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var deckUnits = new List<DeckUnitModel>();

            var tutorialUnits = CreateTutorialUserUnitModels();

            deckUnits.Add(CreateDeckUnitModel(tutorialUnits[0], outpostEnhancement, specialRuleUnitStatusModels,
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>()));
            deckUnits.Add(CreateDeckUnitModel(tutorialUnits[1], outpostEnhancement, specialRuleUnitStatusModels,
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>()));
            deckUnits.Add(CreateDeckUnitModel(tutorialUnits[2], outpostEnhancement, specialRuleUnitStatusModels,
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>()));
            deckUnits.Add(CreateDeckUnitModel(tutorialUnits[3], outpostEnhancement, specialRuleUnitStatusModels,
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>()
                , new Dictionary<MasterDataId, ArtworkEffectValue>()));
            deckUnits.Add(CreateDeckUnitModel(tutorialUnits[4], outpostEnhancement, specialRuleUnitStatusModels,
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>(),
                new Dictionary<MasterDataId, ArtworkEffectValue>()));

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
                NewEncyclopediaFlag.False,
                new UnitGrade(5));

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

        List<DeckUnitModel> InitializePvpOpponentDeckUnits(
            OutpostEnhancementModel outpostEnhancement,
            ArtworkEffectModel artworkEffectModel,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var opponentStatus = OpponentStatusCacheRepository.GetOpponentStatus();
            if (opponentStatus.IsEmpty())
            {
                // 主にPvpでない場合ここに
                return new List<DeckUnitModel>();
            }

            var partyMstCharacterModels = opponentStatus.PvpUnits
                .Select(pvpUnit => MstCharacterDataRepository.GetCharacter(pvpUnit.MstUnitId))
                .ToList();
            // キャラ毎の原画効果ボーナスを計算
            var artworkEffectSummonCoolTimeBonuses = GetArtworkEffectBonus(
                partyMstCharacterModels,
                artworkEffectModel,
                ArtworkEffectType.ResummonSpeedUp);
            var artworkEffectSpecialAttackCoolTimeBonuses = GetArtworkEffectBonus(
                partyMstCharacterModels,
                artworkEffectModel,
                ArtworkEffectType.SpecialAttackChargeSpeedUp);
            var artworkHpUpBonuses = GetArtworkEffectBonus(
                partyMstCharacterModels,
                artworkEffectModel,
                ArtworkEffectType.HpUp);
            var artworkAttackPowerUpBonuses = GetArtworkEffectBonus(
                partyMstCharacterModels,
                artworkEffectModel,
                ArtworkEffectType.AttackPowerUp);

            // プレイヤー側と違いEmptyでIsDeckComponentLock.Trueに設定したDeckUnitの追加は不要のはず
            var deckUnits = new List<DeckUnitModel>();
            foreach (var unit in opponentStatus.PvpUnits)
            {
                var deckUnit = CreateDeckUnitModel(
                    unit,
                    outpostEnhancement,
                    specialRuleUnitStatusModels,
                    artworkEffectSummonCoolTimeBonuses,
                    artworkEffectSpecialAttackCoolTimeBonuses,
                    artworkHpUpBonuses,
                    artworkAttackPowerUpBonuses);
                deckUnits.Add(deckUnit);
            }

            return deckUnits;
        }

        DeckUnitModel CreateDeckUnitModel(
            PvpUnitModel pvpUnitModel,
            OutpostEnhancementModel outpostEnhancementModel,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels,
            IReadOnlyDictionary<MasterDataId, ArtworkEffectValue> artworkEffectSummonCoolTimeBonuses,
            IReadOnlyDictionary<MasterDataId, ArtworkEffectValue> artworkEffectSpecialAttackCoolTimeBonuses,
            IReadOnlyDictionary<MasterDataId, ArtworkEffectValue> artworkHpUpBonuses,
            IReadOnlyDictionary<MasterDataId, ArtworkEffectValue> artworkAttackPowerUpBonuses)
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
            var specialRuleUnitStatus = InGameSpecialRuleUnitStatusProvider.GetSpecialRuleUnitStatus(
                mstCharacter,
                specialRuleUnitStatusModels);

            var artworkSummonBonus = artworkEffectSummonCoolTimeBonuses
                .GetValueOrDefault(mstCharacter.Id, ArtworkEffectValue.Zero)
                .ToTickCount();
            var artworkSpecialAttackBonus = artworkEffectSpecialAttackCoolTimeBonuses
                .GetValueOrDefault(mstCharacter.Id, ArtworkEffectValue.Zero)
                .ToTickCount();
            var artworkHpUpBonus = artworkHpUpBonuses
                .GetValueOrDefault(mstCharacter.Id, ArtworkEffectValue.Zero)
                .ToPercentageM();
            artworkHpUpBonus += PercentageM.Hundred;
            var artworkAttackPowerUpBonus = artworkAttackPowerUpBonuses
                .GetValueOrDefault(mstCharacter.Id, ArtworkEffectValue.Zero)
                .ToPercentageM();
            artworkAttackPowerUpBonus += PercentageM.Hundred;

            var summonCoolTime = UnitSummonCoolTimeCalculator.Calculate(
                mstCharacter,
                outpostEnhancementModel,
                specialRuleUnitStatus.SummonCoolTimeParameter,
                artworkSummonBonus);
            var specialAttackData =
                SpecialRoleSpecialAttackFactory.CreateSpecialRoleSpecialAttack(
                    mstCharacter,
                    pvpUnitModel.UnitGrade,
                    pvpUnitModel.UnitLevel);

            var specialAttackInitialCoolTime = SpecialAttackCoolTimeCalculator.Calculate(
                mstCharacter.SpecialAttackInitialCoolTime,
                specialRuleUnitStatus.SpecialAttackCoolTimeParameter,
                artworkSpecialAttackBonus);

            var specialAttackCoolTime = SpecialAttackCoolTimeCalculator.Calculate(
                mstCharacter.SpecialAttackCoolTime,
                specialRuleUnitStatus.SpecialAttackCoolTimeParameter,
                artworkSpecialAttackBonus);

            return new DeckUnitModel(
                BattleSide.Enemy,
                IsDeckComponentLock.False,
                UserDataId.Empty,
                mstCharacter.Id,
                mstCharacter.MstSeriesId,
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
                specialAttackInitialCoolTime,
                specialAttackCoolTime,
                specialAttackInitialCoolTime,
                specialAttackInitialCoolTime,
                artworkHpUpBonus,
                artworkAttackPowerUpBonus,
                artworkSpecialAttackBonus,
                artworkSummonBonus,
                false);
        }
        #endregion

        Dictionary<MasterDataId, ArtworkEffectValue> GetArtworkEffectBonus(
            IReadOnlyList<MstCharacterModel> partyMstCharacterModels,
            ArtworkEffectModel artworkEffectModel,
            ArtworkEffectType artworkEffectType)
        {
            // キャラ毎の原画効果ボーナスを計算
            Dictionary<MasterDataId, ArtworkEffectValue> artworkEffectDictionary = new();
            foreach (var effectElement in artworkEffectModel.EffectElements)
            {
                // 原画効果の発動条件を満たしているか
                if (effectElement.EffectType == artworkEffectType &&
                    ArtworkEffectActivationEvaluator.EvaluateActivation(
                        effectElement.ActivationRules,
                        partyMstCharacterModels))
                {
                    var randomSeed = GameRepository.GetGameFetchOther().UserInGameStatusModel.InGameRandomSeed;
                    var targetDictionary = ArtworkEffectTargetEvaluator.EvaluateTarget(
                        effectElement.TargetRules,
                        partyMstCharacterModels,
                        randomSeed);
                    foreach (var target in targetDictionary)
                    {
                        if (target.Value)
                        {
                            // artworkEffectSummonCoolTimeBonusesにすでに登録されている場合は加算する
                            if (artworkEffectDictionary.ContainsKey(target.Key))
                            {
                                artworkEffectDictionary[target.Key] += effectElement.EffectValue;
                                continue;
                            }

                            // 初回登録
                            artworkEffectDictionary.Add(
                                target.Key,
                                effectElement.EffectValue);
                        }
                    }
                }
            }

            return artworkEffectDictionary;
        }
    }
}
