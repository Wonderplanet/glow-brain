#if GLOW_INGAME_DEBUG
using System;
using System.Collections.Generic;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Repositories.Debug
{
    public interface IInGameDebugReportRepository
    {
        IReadOnlyList<DebugInGameLogDamageModel> DamageReports { get; }
        Action<DebugInGameLogDamageModel> OnDamageReport { get; set; }
        int CurrentTick { get; }
        IReadOnlyList<CharacterUnitModel> PlayerUnitModels { get; }
        IReadOnlyList<CharacterUnitModel> EnemyUnitModels { get; }
        IReadOnlyList<DeckUnitModel> PlayerDeckUnits { get; }
        IReadOnlyList<DeckUnitModel> EnemyDeckUnits { get; }

        void PushDamageReport(DebugInGameLogDamageModel damageModel);
        void PushTickCount(long currentCount);
        void PushUnitModels(IReadOnlyList<CharacterUnitModel> models);
        void PushPlayerDeckUnits(IReadOnlyList<DeckUnitModel> playerDecks);
        void PushEnemyDeckUnits(IReadOnlyList<DeckUnitModel> enemyDecks);
        //string以外で、Reporter内部で整形したい場合は新規で受け口作る
        void Clear();

    }
}
#endif
