using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

#if GLOW_INGAME_DEBUG
using GLOW.Debugs.InGame.Domain.Models;
#endif // GLOW_INGAME_DEBUG

namespace GLOW.Scenes.InGame.Domain.Models
{
    public interface IInGameScene
    {
        InGameType Type { get; set; }
        StageTimeModel StageTimeModel { get; set; }
        BattleEndModel BattleEndModel { get; set; }
        InGameSettingModel InGameSetting { get; set; }
        MstPageModel MstPage { get; set; }
        MstQuestModel MstQuest { get; set; }
        IMstInGameModel MstInGame { get; set; }
        EventBonusGroupId EventBonusGroupId { get; set; }
        IReadOnlyDictionary<KomaId, KomaModel> KomaDictionary { get; set; }
        IReadOnlyList<DeckUnitModel> DeckUnits { get; set; }
        IReadOnlyList<DeckUnitModel> PvpOpponentDeckUnits { get; set; }
        OutpostModel PlayerOutpost { get; set; }
        OutpostModel EnemyOutpost { get; set; }
        RushModel RushModel { get; set; }
        RushModel PvpOpponentRushModel { get; set; }
        IReadOnlyList<CharacterUnitModel> CharacterUnits { get; set; }
        IReadOnlyList<SpecialUnitModel> SpecialUnits { get; set; }
        IReadOnlyList<MasterDataId> UsedSpecialUnitIdsBeforeNextRush { get; set; }
        SpecialUnitSummonInfoModel SpecialUnitSummonInfoModel { get; set; }
        IReadOnlyList<CharacterUnitModel> DeadUnits { get; set; }
        IReadOnlyList<InGameGimmickObjectModel> GimmickObjects { get; set; }
        IReadOnlyList<GimmickObjectToEnemyTransformationModel> GimmickObjectToEnemyTransformationModels { get; set; }
        DefenseTargetModel DefenseTarget { get; set; }
        IReadOnlyList<IAttackModel> Attacks { get; set; }
        BattlePointModel BattlePointModel { get; set; }
        BattlePointModel PvpOpponentBattlePointModel { get; set; }
        IReadOnlyList<PlacedItemModel> PlacedItems { get; set; }
        BattleOverFlag IsBattleOver { get; set; }
        BattleGiveUpFlag IsBattleGiveUp { get; set; }
        bool IsContinued { get; set; }
        InGameContinueSelectingFlag IsContinueSelecting { get; set; }
        BattleSpeed CurrentBattleSpeed { get; set; }
        IReadOnlyList<BattleSpeed> BattleSpeedList { get; set; }
        OutpostEnhancementModel OutpostEnhancement { get; set; }
        OutpostEnhancementModel PvpOpponentOutpostEnhancement { get; set; }
        HP ArtworkBonusHp { get; set; }
        HP PvpOpponentArtworkBonusHp { get; set; }
        BossAppearancePauseModel BossAppearancePause { get; set; }
        BossSummonQueueModel BossSummonQueue { get; set; }
        UnitSummonQueueModel UnitSummonQueue { get; set; }
        DeckUnitSummonQueueModel DeckUnitSummonQueue { get; set; }
        SpecialUnitSummonQueueModel SpecialUnitSummonQueue { get; set; }
        IReadOnlyList<MangaAnimationModel> MangaAnimations { get; set; }
        DefeatEnemyCount DefeatEnemyCount { get; set; }
        NoContinueFlag IsNoContinue { get; set; }
        InGameScoreModel ScoreModel { get; set; }
        ScoreCalculateModel ScoreCalculateModel { get; set; }
        IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> SpecialRuleUnitStatusModels { get; set; }

#if GLOW_INGAME_DEBUG
        InGameDebugModel Debug { get; set; }
#endif
    }
}
