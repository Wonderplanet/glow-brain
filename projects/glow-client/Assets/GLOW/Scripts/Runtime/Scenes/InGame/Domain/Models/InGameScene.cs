using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

#if GLOW_INGAME_DEBUG
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Repositories.Debug;
using Zenject;
#endif

namespace GLOW.Scenes.InGame.Domain.Models
{
    public class InGameScene : IInGameScene
    {
#if GLOW_INGAME_DEBUG
        [InjectOptional] IInGameDebugReportRepository DebugReportRepository { get; }
#endif
        public StageTimeModel StageTimeModel { get; set; } = StageTimeModel.Zero;
        public BattleEndModel BattleEndModel { get; set; } = BattleEndModel.Empty;
        public InGameSettingModel InGameSetting { get; set; } = InGameSettingModel.Default;
        public MstPageModel MstPage { get; set; } = MstPageModel.Empty;
        public MstQuestModel MstQuest { get; set; } = MstQuestModel.Empty;
        public IMstInGameModel MstInGame { get; set; } = MstStageModel.Empty;
        public EventBonusGroupId EventBonusGroupId { get; set; } = EventBonusGroupId.Empty;
        public IReadOnlyDictionary<KomaId, KomaModel> KomaDictionary { get; set; } = new Dictionary<KomaId, KomaModel>();

        IReadOnlyList<DeckUnitModel> _deckUnits = new List<DeckUnitModel>();
        public IReadOnlyList<DeckUnitModel> DeckUnits
        {
            get => _deckUnits;
            set
            {
                _deckUnits = value;
#if GLOW_INGAME_DEBUG
                DebugReportRepository.PushPlayerDeckUnits(value);
#endif
            }
        }

        IReadOnlyList<DeckUnitModel> _pvpOpponentDeckUnits = new List<DeckUnitModel>();
        public IReadOnlyList<DeckUnitModel> PvpOpponentDeckUnits
        {
            get => _pvpOpponentDeckUnits;
            set
            {
                _pvpOpponentDeckUnits = value;
#if GLOW_INGAME_DEBUG
                DebugReportRepository.PushEnemyDeckUnits(value);
#endif
            }
        }

        public OutpostModel PlayerOutpost { get; set; } = OutpostModel.Empty;
        public OutpostModel EnemyOutpost { get; set; } = OutpostModel.Empty;
        public RushModel RushModel { get; set; } = RushModel.Empty;
        public RushModel PvpOpponentRushModel { get; set; } = RushModel.Empty;
        public BattleGiveUpFlag IsBattleGiveUp { get; set; } = BattleGiveUpFlag.False;

        IReadOnlyList<CharacterUnitModel> _characterUnits = new List<CharacterUnitModel>();
        public IReadOnlyList<CharacterUnitModel> CharacterUnits
        {
            get
            {
                return _characterUnits;
            }
            set
            {
                _characterUnits = value;
#if GLOW_INGAME_DEBUG
                DebugReportRepository?.PushUnitModels(value);
#endif
            }
        }

        public IReadOnlyList<SpecialUnitModel> SpecialUnits { get; set; } = new List<SpecialUnitModel>();
        public IReadOnlyList<MasterDataId> UsedSpecialUnitIdsBeforeNextRush { get; set; } = new List<MasterDataId>();
        public SpecialUnitSummonInfoModel SpecialUnitSummonInfoModel { get; set; } = SpecialUnitSummonInfoModel.Empty;
        public IReadOnlyList<CharacterUnitModel> DeadUnits { get; set; } = new List<CharacterUnitModel>();
        public IReadOnlyList<InGameGimmickObjectModel> GimmickObjects { get; set; } = new List<InGameGimmickObjectModel>();
        public IReadOnlyList<GimmickObjectToEnemyTransformationModel> GimmickObjectToEnemyTransformationModels { get; set; } =
            new List<GimmickObjectToEnemyTransformationModel>();
        public DefenseTargetModel DefenseTarget { get; set; } = DefenseTargetModel.Empty;
        public IReadOnlyList<IAttackModel> Attacks { get; set; } = new List<IAttackModel>();
        public BattlePointModel BattlePointModel { get; set; } = BattlePointModel.Empty;
        public BattlePointModel PvpOpponentBattlePointModel { get; set; } = BattlePointModel.Empty;
        public IReadOnlyList<PlacedItemModel> PlacedItems { get; set; } = new List<PlacedItemModel>();
        public BattleOverFlag IsBattleOver { get; set; } = BattleOverFlag.False;
        public bool IsContinued { get; set; }
        public InGameContinueSelectingFlag IsContinueSelecting { get; set; }
        public BattleSpeed CurrentBattleSpeed { get; set; } = BattleSpeed.x1;
        public IReadOnlyList<BattleSpeed> BattleSpeedList { get; set; } = new List<BattleSpeed>()
        {
            BattleSpeed.x1,
            BattleSpeed.x1_5,
            BattleSpeed.x2
        };

        public OutpostEnhancementModel OutpostEnhancement { get; set; } = OutpostEnhancementModel.Empty;
        public OutpostEnhancementModel PvpOpponentOutpostEnhancement { get; set; } = OutpostEnhancementModel.Empty;
        public HP ArtworkBonusHp { get; set; } = HP.Empty;
        public HP PvpOpponentArtworkBonusHp { get; set; } = HP.Empty;
        public BossAppearancePauseModel BossAppearancePause { get; set; } = BossAppearancePauseModel.Empty;
        public BossSummonQueueModel BossSummonQueue { get; set; } = BossSummonQueueModel.Empty;
        public UnitSummonQueueModel UnitSummonQueue { get; set; } = UnitSummonQueueModel.Empty;
        public DeckUnitSummonQueueModel DeckUnitSummonQueue { get; set; } = DeckUnitSummonQueueModel.Empty;
        public SpecialUnitSummonQueueModel SpecialUnitSummonQueue { get; set; } = SpecialUnitSummonQueueModel.Empty;
        public IReadOnlyList<MangaAnimationModel> MangaAnimations { get; set; } = new List<MangaAnimationModel>();
        public DefeatEnemyCount DefeatEnemyCount { get; set; } = DefeatEnemyCount.Zero;
        public NoContinueFlag IsNoContinue { get; set; } = NoContinueFlag.Empty;

        public InGameType Type { get; set; }

        public InGameScoreModel ScoreModel { get; set; } = InGameScoreModel.Empty;
        public ScoreCalculateModel ScoreCalculateModel { get; set; } = ScoreCalculateModel.Empty;

        public IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> SpecialRuleUnitStatusModels { get; set; } =
            new List<MstInGameSpecialRuleUnitStatusModel>();

        public InGameConsumptionType InGameConsumptionType { get; set; } = InGameConsumptionType.Stamina;

#if GLOW_INGAME_DEBUG
        public InGameDebugModel Debug { get; set; } = InGameDebugModel.Empty;
#endif
    }
}
