#if GLOW_DEBUG
using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Debugs.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Debugs.Home.Domain.Constants
{
    public class DebugMstUnitTemporaryParameterDefinitions
    {
        public static readonly MasterDataId DebugDummyIdTemplateA = new MasterDataId("debug_dummy_01");
        public static readonly MasterDataId DebugDummyIdTemplateB = new MasterDataId("debug_dummy_02");
        public static readonly MasterDataId DebugDummyIdTemplateC = new MasterDataId("debug_dummy_03");

        public static IReadOnlyList<MasterDataId> DebugDummyIds = new List<MasterDataId>
        {
            DebugDummyIdTemplateA,
            DebugDummyIdTemplateB,
            DebugDummyIdTemplateC,
        };

        public static readonly UserDataId DebugUserDataIdTemplateA = new UserDataId("debug_user_data_01");
        public static readonly UserDataId DebugUserDataIdTemplateB = new UserDataId("debug_user_data_02");
        public static readonly UserDataId DebugUserDataIdTemplateC = new UserDataId("debug_user_data_03");

        public static AttackElement TemplateAttackElement = new AttackElement(
            new MasterDataId("chara_dan_00101_Normal_00000"),
            TickCount.Empty,
            TickCount.Empty,
            AttackType.Direct,
            new AttackRange(
                AttackRangePointType.Distance,
                new AttackRangeParameter(0f),
                AttackRangePointType.Distance,
                new AttackRangeParameter(0.5f)),
            new FieldObjectCount(100),
            AttackViewId.Empty,
            AttackTarget.Foe,
            AttackTargetType.All,
            (CharacterColor[])Enum.GetValues(typeof(CharacterColor)),
            (CharacterUnitRoleType[])Enum.GetValues(typeof(CharacterUnitRoleType)),
            Array.Empty<MasterDataId>(),
            Array.Empty<MasterDataId>(),
            AttackDamageType.Damage,
            new AttackHitData(
                AttackHitType.Normal,
                new AttackHitParameter(1000),
                new AttackHitParameter(1000),
                AttackHitBattleEffectId.Empty,
                new List<AttackHitOnomatopoeiaAssetKey> { new AttackHitOnomatopoeiaAssetKey("Do") },
                new SoundEffectAssetKey("SSE_051_004"),
                new SoundEffectAssetKey("SSE_051_013"),
                AccumulatedDamageKnockBackFlag.True
            ),
            AttackHitStopFlag.True,
            Percentage.Hundred,
            new AttackPowerParameter(AttackPowerParameterType.Percentage, 75),
            new StateEffect(
                StateEffectType.None,
                new EffectiveCount(0),
                new EffectiveProbability(0),
                new TickCount(0),
                new StateEffectParameter(0),
                new StateEffectConditionValue(string.Empty),
                new StateEffectConditionValue(string.Empty)),
            Array.Empty<AttackSubElement>()
        );

        #region MstCharacterTemplateModel

        public static MstCharacterModel TemplateMstCharacterDummyModel = new MstCharacterModel(
            new MasterDataId("debug_dummy_01"),
            new CharacterName("モモ"),
            new UnitDescription(
                "宇宙人は信じていないが幽霊は信じている、霊媒師の家系の女子高生。セルポ星人にさらわれた時に超能力に目覚める。困っている人を見過ごせない優しい一面もあり、誰にも裏表なく接することができる。憧れの人のような硬派な男性が好きと自称しており、オカルンと憧れの人との共通点を知ってときめいてしまうことも。"),
            new UnitInfoDetail("必殺ワザで複数の相手にダメージを与え、さらに相手をノックバックさせることができるぞ!"),
            CharacterUnitRoleType.Attack,
            CharacterColor.Red,
            CharacterAttackRangeType.Middle,
            UnitLabel.PremiumSSR,
            new MasterDataId("piece_dan_00101"),
            new MasterDataId("dan"),
            new SeriesAssetKey("dan"),
            new UnitAssetKey("chara_dan_00101"),
            Rarity.SSR,
            new SortOrder(1),
            IsEncyclopediaSpecialAttackPositionRight.False,
            HasSpecificRankUpFlag.False,
            new BattlePoint(10),
            new TickCount(255),
            new HP(690),
            new HP(6900),
            new KnockBackCount(2),
            new UnitMoveSpeed(35),
            new WellDistance(0.39f),
            new AttackPower(1000),
            new AttackPower(1000),
            new CharacterColorAdvantageAttackBonus(1.5f),
            new CharacterColorAdvantageDefenseBonus(0.7f),
            new MstAttackModel(
                new MasterDataId("chara_dan_00101_Normal_00000"),
                new AttackData(
                    new TickCount(25),
                    new AttackBaseData(
                        Array.Empty<CharacterColor>(),
                        KillerPercentage.Empty,
                        new TickCount(50),
                        new TickCount(75)),
                    new[]
                    {
                        new AttackElement(
                            new MasterDataId("chara_dan_00101_Normal_00000"),
                            TickCount.Empty,
                            TickCount.Empty,
                            AttackType.Direct,
                            new AttackRange(
                                AttackRangePointType.Distance,
                                new AttackRangeParameter(0f),
                                AttackRangePointType.Distance,
                                new AttackRangeParameter(0.5f)),
                            new FieldObjectCount(100),
                            AttackViewId.Empty,
                            AttackTarget.Foe,
                            AttackTargetType.All,
                            (CharacterColor[])Enum.GetValues(typeof(CharacterColor)),
                            (CharacterUnitRoleType[])Enum.GetValues(typeof(CharacterUnitRoleType)),
                            Array.Empty<MasterDataId>(),
                            Array.Empty<MasterDataId>(),
                            AttackDamageType.Damage,
                            new AttackHitData(
                                AttackHitType.Normal,
                                new AttackHitParameter(1000),
                                new AttackHitParameter(1000),
                                AttackHitBattleEffectId.Empty,
                                new List<AttackHitOnomatopoeiaAssetKey> { new AttackHitOnomatopoeiaAssetKey("Do") },
                                new SoundEffectAssetKey("SSE_051_004"),
                                new SoundEffectAssetKey("SSE_051_013"),
                                AccumulatedDamageKnockBackFlag.True
                            ),
                            AttackHitStopFlag.False,
                            Percentage.Hundred,
                            new AttackPowerParameter(AttackPowerParameterType.Percentage, 75),
                            new StateEffect(
                                StateEffectType.None,
                                new EffectiveCount(0),
                                new EffectiveProbability(0),
                                new TickCount(0),
                                new StateEffectParameter(0),
                                new StateEffectConditionValue(string.Empty),
                                new StateEffectConditionValue(string.Empty)),
                            Array.Empty<AttackSubElement>()
                        )
                    })),
            new List<MstSpecialAttackModel>()
            {
                new MstSpecialAttackModel(
                    new MasterDataId("chara_dan_00101_Special_00001"),
                    new MasterDataId("debug_dummy_01"),
                    new UnitGrade(1),
                    new SpecialAttackName("霊媒パンチ"),
                    new SpecialAttackInfoDescription("前方の敵に強力な一撃を与え、ノックバックさせる"),
                    new SpecialAttackInfoGradeDescription("Grade1特殊攻撃の詳細説明"),
                    new AttackData(
                        new TickCount(30),
                        new AttackBaseData(
                            new[] { CharacterColor.Blue, CharacterColor.Green },
                            new KillerPercentage(150),
                            new TickCount(60),
                            new TickCount(90)
                        ),
                        new[]
                        {
                            new AttackElement(
                                new MasterDataId("chara_dan_00101_Special_Element_00001"),
                                new TickCount(0),
                                TickCount.Empty,
                                AttackType.Direct,
                                new AttackRange(
                                    AttackRangePointType.Distance,
                                    new AttackRangeParameter(0.2f),
                                    AttackRangePointType.Distance,
                                    new AttackRangeParameter(1.5f)
                                ),
                                new FieldObjectCount(5),
                                new AttackViewId(1001),
                                AttackTarget.Foe,
                                AttackTargetType.All,
                                (CharacterColor[])Enum.GetValues(typeof(CharacterColor)),
                                (CharacterUnitRoleType[])Enum.GetValues(typeof(CharacterUnitRoleType)),
                                Array.Empty<MasterDataId>(),
                                Array.Empty<MasterDataId>(),
                                AttackDamageType.Damage,
                                new AttackHitData(
                                    AttackHitType.Normal,
                                    new AttackHitParameter(120),
                                    new AttackHitParameter(120),
                                    AttackHitBattleEffectId.Empty,
                                    new List<AttackHitOnomatopoeiaAssetKey>
                                    {
                                        new AttackHitOnomatopoeiaAssetKey("Do"),
                                    },
                                    new SoundEffectAssetKey("SSE_051_004"),
                                    new SoundEffectAssetKey("SSE_052_013"),
                                    AccumulatedDamageKnockBackFlag.True
                                ),
                                AttackHitStopFlag.False,
                                new Percentage(100),
                                new AttackPowerParameter(AttackPowerParameterType.Percentage, 200),
                                new StateEffect(
                                    StateEffectType.None,
                                    new EffectiveCount(1),
                                    new EffectiveProbability(100),
                                    new TickCount(60),
                                    new StateEffectParameter(3),
                                    new StateEffectConditionValue(string.Empty),
                                    new StateEffectConditionValue(string.Empty)
                                ),
                                Array.Empty<AttackSubElement>()
                            )
                        }
                    ),
                    Array.Empty<SpecialRoleLevelUpAttackElement>()
                ),
                new MstSpecialAttackModel(
                    new MasterDataId("chara_dan_00101_Special_00002"),
                    new MasterDataId("debug_dummy_01"),
                    new UnitGrade(2),
                    new SpecialAttackName("超霊媒パンチ"),
                    new SpecialAttackInfoDescription("広範囲の敵に超強力な一撃を与え、大きくノックバックさせる"),
                    new SpecialAttackInfoGradeDescription("Grade2特殊攻撃の詳細説明"),
                    new AttackData(
                        new TickCount(35),
                        new AttackBaseData(
                            new[] { CharacterColor.Blue, CharacterColor.Green, CharacterColor.Red },
                            new KillerPercentage(200),
                            new TickCount(70),
                            new TickCount(100)
                        ),
                        new[]
                        {
                            new AttackElement(
                                new MasterDataId("chara_dan_00101_Special_Element_00002"),
                                new TickCount(0),
                                new TickCount(30),
                                AttackType.Direct,
                                new AttackRange(
                                    AttackRangePointType.Distance,
                                    new AttackRangeParameter(0f),
                                    AttackRangePointType.Distance,
                                    new AttackRangeParameter(0.5f)
                                ),
                                new FieldObjectCount(10),
                                new AttackViewId(1002),
                                AttackTarget.Foe,
                                AttackTargetType.All,
                                (CharacterColor[])Enum.GetValues(typeof(CharacterColor)),
                                (CharacterUnitRoleType[])Enum.GetValues(typeof(CharacterUnitRoleType)),
                                Array.Empty<MasterDataId>(),
                                Array.Empty<MasterDataId>(),
                                AttackDamageType.Damage,
                                new AttackHitData(
                                    AttackHitType.KnockBack1,
                                    new AttackHitParameter(0),
                                    new AttackHitParameter(0),
                                    AttackHitBattleEffectId.Empty,
                                    new List<AttackHitOnomatopoeiaAssetKey>
                                    {
                                        new AttackHitOnomatopoeiaAssetKey("Do")
                                    },
                                    new SoundEffectAssetKey("SSE_051_004"),
                                    new SoundEffectAssetKey("SSE_052_013"),
                                    AccumulatedDamageKnockBackFlag.True
                                ),
                                AttackHitStopFlag.True,
                                new Percentage(100),
                                new AttackPowerParameter(AttackPowerParameterType.Percentage, 112),
                                new StateEffect(
                                    StateEffectType.None,
                                    new EffectiveCount(0),
                                    new EffectiveProbability(100),
                                    new TickCount(90),
                                    new StateEffectParameter(5),
                                    new StateEffectConditionValue(string.Empty),
                                    new StateEffectConditionValue(string.Empty)
                                ),
                                Array.Empty<AttackSubElement>()
                            )
                        }
                    ),
                    Array.Empty<SpecialRoleLevelUpAttackElement>()
                ),
            },
            new TickCount(120),
            new TickCount(520),
            new List<MstUnitAbilityModel>()
            {
                new MstUnitAbilityModel(
                    new MasterDataId("2"),
                    new UnitAbility(
                        UnitAbilityType.AttackPowerUpInNormalKoma,
                        new UnitAbilityAssetKey("AttackPowerUpInNormalKoma"),
                        new UnitAbilityParameter("14"),
                        new UnitAbilityParameter("15"),
                        new UnitAbilityParameter("16"),
                        string.Empty,
                        new UnitRank(1))
                ),
                new MstUnitAbilityModel(
                    new MasterDataId("3"),
                    new UnitAbility(
                        UnitAbilityType.GustKomaBlock,
                        new UnitAbilityAssetKey("GustKomaBlock"),
                        new UnitAbilityParameter("17"),
                        new UnitAbilityParameter("18"),
                        new UnitAbilityParameter("19"),
                        string.Empty,
                        new UnitRank(1))
                ),
                new MstUnitAbilityModel(
                    new MasterDataId("4"),
                    new UnitAbility(
                        UnitAbilityType.FreezeBlock,
                        new UnitAbilityAssetKey("FreezeBlock"),
                        new UnitAbilityParameter("20"),
                        new UnitAbilityParameter("21"),
                        new UnitAbilityParameter("22"),
                        string.Empty,
                        new UnitRank(1))
                ),
                new MstUnitAbilityModel(
                    new MasterDataId("5"),
                    new UnitAbility(
                        UnitAbilityType.BurnDamageCut,
                        new UnitAbilityAssetKey("BurnDamageCut"),
                        new UnitAbilityParameter("23"),
                        new UnitAbilityParameter("24"),
                        new UnitAbilityParameter("25"),
                        string.Empty,
                        new UnitRank(2))
                )
            },
            new List<SpeechBalloonModel>
            {
                SpeechBalloonModel.Empty with
                {
                    SpeechBalloonText = SpeechBalloonText.Empty with { Text = "SpeechText1" }
                },
            });

        #endregion

        public static MstCharacterModel DebugMstCharacterDummyTemplateA = TemplateMstCharacterDummyModel with
        {
            Id = DebugDummyIdTemplateA,
            Name = new CharacterName("デバック用ユニットA＿ノーマル"),
        };

        public static MstCharacterModel DebugMstCharacterDummyTemplateB = TemplateMstCharacterDummyModel with
        {
            Id = DebugDummyIdTemplateB,
            Name = new CharacterName("デバック用ユニットB＿ノーマル"),
        };

        public static MstCharacterModel DebugMstCharacterDummyTemplateC = TemplateMstCharacterDummyModel with
        {
            Id = DebugDummyIdTemplateC,
            Name = new CharacterName("デバック用ユニットC＿スペシャル"),
            RoleType = CharacterUnitRoleType.Special,
        };

        public static MstCharacterModel DebugDummyCharacterModelA = DebugMstCharacterDummyTemplateA;
        public static MstCharacterModel DebugDummyCharacterModelB = DebugMstCharacterDummyTemplateB;
        public static MstCharacterModel DebugDummyCharacterModelC = DebugMstCharacterDummyTemplateC;

        public static IReadOnlyList<MstCharacterModel> DebugMstCharacterDummyTemplates = new List<MstCharacterModel>
        {
            DebugDummyCharacterModelA,
            DebugDummyCharacterModelB,
            DebugDummyCharacterModelC,
        };

        public static readonly IReadOnlyList<UserUnitModel> DebugUserUnitModels = new List<UserUnitModel>
        {
            UserUnitModel.Empty with
            {
                MstUnitId = DebugDummyIdTemplateA,
                UsrUnitId = DebugUserDataIdTemplateA,
                Level = UnitLevel.One,
                Rank = UnitRank.Min,
                Grade = UnitGrade.Minimum,
            },
            UserUnitModel.Empty with
            {
                MstUnitId = DebugDummyIdTemplateB,
                UsrUnitId = DebugUserDataIdTemplateB,
                Level = UnitLevel.One,
                Rank = UnitRank.Min,
                Grade = UnitGrade.Minimum,
            },
            UserUnitModel.Empty with
            {
                MstUnitId = DebugDummyIdTemplateC,
                UsrUnitId = DebugUserDataIdTemplateC,
                Level = UnitLevel.One,
                Rank = UnitRank.Min,
                Grade = UnitGrade.Minimum,
            },
        };

        public static readonly DebugSummonTargetId DebugSummonTargetIdTemplateA =
            new DebugSummonTargetId("summon_01");

        public static readonly DebugSummonTargetId DebugSummonTargetIdTemplateB =
            new DebugSummonTargetId("summon_2");

        public static readonly DebugSummonTargetId DebugSummonTargetIdTemplateC =
            new DebugSummonTargetId("summon_03");

        public static UnitAssetKey DebugSummonUnitAssetKeyA = new UnitAssetKey("enemy_spy_00101");
        public static UnitAssetKey DebugSummonUnitAssetKeyB = new UnitAssetKey("enemy_spy_00101");
        public static UnitAssetKey DebugSummonUnitAssetKeyC = new UnitAssetKey("enemy_spy_00101");

        public static DebugEnemyInfoModel TemplateEnemyInfoModelA = DebugEnemyInfoModel.Empty with
        {
            SummonTargetId = DebugSummonTargetIdTemplateA,
            EnemyName = new CharacterName("デバック用サモンA＿ノーマル"),
            UnitKind = CharacterUnitKind.Normal,
        };

        public static DebugEnemyInfoModel TemplateEnemyInfoModelB = DebugEnemyInfoModel.Empty with
        {
            SummonTargetId = DebugSummonTargetIdTemplateB,
            EnemyName = new CharacterName("デバック用サモンB＿ノーマル"),
            UnitKind = CharacterUnitKind.Normal,
        };

        public static DebugEnemyInfoModel TemplateEnemyInfoModelC = DebugEnemyInfoModel.Empty with
        {
            SummonTargetId = DebugSummonTargetIdTemplateC,
            EnemyName = new CharacterName("デバック用サモンC＿ノーマル"),
            UnitKind = CharacterUnitKind.Normal,
        };

        public static List<DebugEnemyInfoModel> DebugEnemyInfoModels = new List<DebugEnemyInfoModel>
        {
            TemplateEnemyInfoModelA,
            TemplateEnemyInfoModelB,
            TemplateEnemyInfoModelC,
        };

        #region TemplateAutoPlayerSequenceElementModel

        public static MstAutoPlayerSequenceElementModel DebugAutoPlayerSequenceElementModelTemplate =
            new MstAutoPlayerSequenceElementModel(
                new AutoPlayerSequenceSetId("01_dan_normal_1"),
                new AutoPlayerSequenceElementId("1"),
                new AutoPlayerSequenceGroupId("AutoPlayerSequenceGroupId"),
                AutoPlayerSequenceElementId.Empty,
                new SequenceCondition(AutoPlayerSequenceConditionType.ElapsedTime,
                    new AutoPlayerSequenceConditionValue("650")),
                new SequenceCondition(AutoPlayerSequenceConditionType.None, AutoPlayerSequenceConditionValue.Empty),
                new AutoPlayerSequenceAction(AutoPlayerSequenceActionType.SummonEnemy
                    , new AutoPlayerSequenceActionValue("e_spy_00101_general_n_Normal_Colorless"),
                    AutoPlayerSequenceActionValue.Empty),
                SummonAnimationType.None,
                new AutoPlayerSequenceSummonCount(1),
                new TickCount(0),
                new TickCount(0),
                FieldCoordV2.Empty,
                MoveStartConditionType.None,
                new MoveStartConditionValue(0),
                MoveStopConditionType.None,
                new MoveStopConditionValue(0),
                MoveStartConditionType.None,
                new MoveStartConditionValue(0),
                new MoveLoopCount(0),
                UnitAuraType.Default,
                UnitDeathType.Normal,
                new DropBattlePoint(0),
                new InGameScore(1),
                new AutoPlayerSequenceCoefficient(1),
                new AutoPlayerSequenceCoefficient(1),
                new AutoPlayerSequenceCoefficient(1),
                OutpostDamageInvalidationFlag.False
            );

        #endregion

        #region TemplateEnemyStageParameterModel

        public static MstEnemyStageParameterModel DebugEnemyStageParameterModel = new MstEnemyStageParameterModel(
            new MasterDataId("e_spy_00101_general_n_Normal_Colorless"),
            new MasterDataId("e_spy_00101_general_n_Normal_Colorless"),
            new CharacterName("グエン"),
            CharacterUnitKind.Normal,
            CharacterUnitRoleType.Attack,
            CharacterColor.Colorless,
            new MasterDataId("4"),
            new UnitAssetKey("enemy_spy_00101"),
            new SortOrder(13),
            new HP(1500),
            new KnockBackCount(1),
            new UnitMoveSpeed(31),
            new WellDistance(0.2f),
            new AttackPower(100),
            new CharacterColorAdvantageAttackBonus(1.5f),
            new CharacterColorAdvantageDefenseBonus(0.7f),
            new AttackData(
                new TickCount(25),
                new AttackBaseData(
                    Array.Empty<CharacterColor>(),
                    new KillerPercentage(0),
                    new TickCount(60),
                    new TickCount(50)
                ),
                new List<AttackElement>
                {
                    new AttackElement(
                        new MasterDataId("e_spy_00101_general_n_Normal_Colorless_Normal_00000_1"),
                        TickCount.Empty,
                        TickCount.Empty,
                        AttackType.Direct,
                        new AttackRange(
                            AttackRangePointType.Distance,
                            new AttackRangeParameter(0f),
                            AttackRangePointType.Distance,
                            new AttackRangeParameter(0.21f)
                        ),
                        new FieldObjectCount(1),
                        AttackViewId.Empty,
                        AttackTarget.Foe,
                        AttackTargetType.All,
                        (CharacterColor[])Enum.GetValues(typeof(CharacterColor)),
                        (CharacterUnitRoleType[])Enum.GetValues(typeof(CharacterUnitRoleType)),
                        Array.Empty<MasterDataId>(),
                        Array.Empty<MasterDataId>(),
                        AttackDamageType.Damage,
                        new AttackHitData(
                            AttackHitType.Normal,
                            new AttackHitParameter(0),
                            new AttackHitParameter(0),
                            AttackHitBattleEffectId.Empty,
                            new List<AttackHitOnomatopoeiaAssetKey> { new AttackHitOnomatopoeiaAssetKey("Do") },
                            new SoundEffectAssetKey("SSE_051_004"),
                            new SoundEffectAssetKey("SSE_051_013"),
                            AccumulatedDamageKnockBackFlag.True
                        ),
                        AttackHitStopFlag.False,
                        new Percentage(100),
                        new AttackPowerParameter(AttackPowerParameterType.Percentage, 100),
                        new StateEffect(
                            StateEffectType.None,
                            new EffectiveCount(0),
                            new EffectiveProbability(0),
                            new TickCount(0),
                            new StateEffectParameter(0),
                            new StateEffectConditionValue(string.Empty),
                            new StateEffectConditionValue(string.Empty)
                        ),
                        new List<AttackSubElement>()
                    )
                }
            ),
            new AttackData(
                        new TickCount(30),
                        new AttackBaseData(
                            new[] { CharacterColor.Blue, CharacterColor.Green },
                            new KillerPercentage(150),
                            new TickCount(60),
                            new TickCount(90)
                        ),
                        new[]
                        {
                            new AttackElement(
                                new MasterDataId("chara_dan_00101_Special_Element_00001"),
                                new TickCount(0),
                                TickCount.Empty,
                                AttackType.Direct,
                                new AttackRange(
                                    AttackRangePointType.Distance,
                                    new AttackRangeParameter(0.2f),
                                    AttackRangePointType.Distance,
                                    new AttackRangeParameter(1.5f)
                                ),
                                new FieldObjectCount(5),
                                new AttackViewId(1001),
                                AttackTarget.Foe,
                                AttackTargetType.All,
                                (CharacterColor[])Enum.GetValues(typeof(CharacterColor)),
                                (CharacterUnitRoleType[])Enum.GetValues(typeof(CharacterUnitRoleType)),
                                Array.Empty<MasterDataId>(),
                                Array.Empty<MasterDataId>(),
                                AttackDamageType.Damage,
                                new AttackHitData(
                                    AttackHitType.Normal,
                                    new AttackHitParameter(120),
                                    new AttackHitParameter(120),
                                    AttackHitBattleEffectId.Empty,
                                    new List<AttackHitOnomatopoeiaAssetKey>
                                    {
                                        new AttackHitOnomatopoeiaAssetKey("Do"),
                                    },
                                    new SoundEffectAssetKey("SSE_051_004"),
                                    new SoundEffectAssetKey("SSE_052_013"),
                                    AccumulatedDamageKnockBackFlag.True
                                ),
                                AttackHitStopFlag.False,
                                new Percentage(100),
                                new AttackPowerParameter(AttackPowerParameterType.Percentage, 200),
                                new StateEffect(
                                    StateEffectType.None,
                                    new EffectiveCount(1),
                                    new EffectiveProbability(100),
                                    new TickCount(60),
                                    new StateEffectParameter(3),
                                    new StateEffectConditionValue(string.Empty),
                                    new StateEffectConditionValue(string.Empty)
                                ),
                                Array.Empty<AttackSubElement>()
                            )
                        }
                    ),
            AttackData.Empty,
            AttackComboCycle.Empty,
            UnitAbility.Empty,
            new DropBattlePoint(200),
            UnitTransformationParameter.Empty
        );

        #endregion

        public static AutoPlayerSequenceActionValue DebugSummonActionValueA =
            new AutoPlayerSequenceActionValue("e_debug_summon_a");

        public static AutoPlayerSequenceActionValue DebugSummonActionValueB =
            new AutoPlayerSequenceActionValue("e_debug_summon_b");

        public static AutoPlayerSequenceActionValue DebugSummonActionValueC =
            new AutoPlayerSequenceActionValue("e_debug_summon_c");

        public static MstEnemyStageParameterModel DebugEnemyStageParameterModelA = DebugEnemyStageParameterModel with
        {
            Id = DebugSummonActionValueA.ToMasterDataId(),
            Name = new CharacterName("デバック用エネミーA＿ノーマル"),
            AssetKey = DebugSummonUnitAssetKeyA,
        };

        public static MstEnemyStageParameterModel DebugEnemyStageParameterModelB = DebugEnemyStageParameterModel with
        {
            Id = DebugSummonActionValueB.ToMasterDataId(),
            Name = new CharacterName("デバック用エネミーB＿ノーマル"),
            AssetKey = DebugSummonUnitAssetKeyB,
        };

        public static MstEnemyStageParameterModel DebugEnemyStageParameterModelC = DebugEnemyStageParameterModel with
        {
            Id = DebugSummonActionValueC.ToMasterDataId(),
            Name = new CharacterName("デバック用エネミーC＿ノーマル"),
            AssetKey = DebugSummonUnitAssetKeyC,
        };

        public static List<MstEnemyStageParameterModel> DebugEnemyStageParameterModels =
            new List<MstEnemyStageParameterModel>
            {
                DebugEnemyStageParameterModelA,
                DebugEnemyStageParameterModelB,
                DebugEnemyStageParameterModelC,
            };

        public static AutoPlayerSequenceSetId DebugSequenceId =
            new AutoPlayerSequenceSetId("debug_auto_player_sequence");

        public static MstAutoPlayerSequenceElementModel DebugAutoPlayerSequenceElementModelA =
            DebugAutoPlayerSequenceElementModelTemplate with
            {
                SequenceSetId = DebugSequenceId,
                SequenceElementId = DebugSummonTargetIdTemplateA.ToAutoPlayerSequenceElementId(),
                Action = DebugAutoPlayerSequenceElementModelTemplate.Action with
                {
                    Value = DebugSummonActionValueA
                },
            };

        public static MstAutoPlayerSequenceElementModel DebugAutoPlayerSequenceElementModelB =
            DebugAutoPlayerSequenceElementModelTemplate with
            {
                SequenceSetId = DebugSequenceId,
                SequenceElementId = DebugSummonTargetIdTemplateB.ToAutoPlayerSequenceElementId(),
                Action = DebugAutoPlayerSequenceElementModelTemplate.Action with
                {
                    Value = DebugSummonActionValueB
                },
            };

        public static MstAutoPlayerSequenceElementModel DebugAutoPlayerSequenceElementModelC =
            DebugAutoPlayerSequenceElementModelTemplate with
            {
                SequenceSetId = DebugSequenceId,
                SequenceElementId = DebugSummonTargetIdTemplateC.ToAutoPlayerSequenceElementId(),
                Action = DebugAutoPlayerSequenceElementModelTemplate.Action with
                {
                    Value = DebugSummonActionValueC
                },
            };

        public static List<MstAutoPlayerSequenceElementModel> DebugAutoPlayerSequenceElementModels =
            new List<MstAutoPlayerSequenceElementModel>
            {
                DebugAutoPlayerSequenceElementModelA,
                DebugAutoPlayerSequenceElementModelB,
                DebugAutoPlayerSequenceElementModelC,
            };
    }
}
#endif
