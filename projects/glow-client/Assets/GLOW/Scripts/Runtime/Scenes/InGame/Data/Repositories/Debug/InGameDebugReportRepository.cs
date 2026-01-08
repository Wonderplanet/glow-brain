#if GLOW_INGAME_DEBUG
using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Repositories.Debug;

namespace GLOW.Scenes.InGame.Data.Repositories.Debug
{
    public class InGameDebugReportRepository : IInGameDebugReportRepository
    {
        readonly List<DebugInGameLogDamageModel> _damageReports = new();
        long _currentTick;
        IReadOnlyList<CharacterUnitModel> _playerUnits;
        IReadOnlyList<CharacterUnitModel> _enemyUnits;
        IReadOnlyList<DeckUnitModel> _playerDeckUnits = new List<DeckUnitModel>();
        IReadOnlyList<DeckUnitModel> _enemyDeckUnits = new List<DeckUnitModel>();

        public Action<DebugInGameLogDamageModel> OnDamageReport { get; set; }
        IReadOnlyList<DebugInGameLogDamageModel> IInGameDebugReportRepository.DamageReports => _damageReports;
        int IInGameDebugReportRepository.CurrentTick => (int)_currentTick;
        IReadOnlyList<CharacterUnitModel> IInGameDebugReportRepository.PlayerUnitModels =>_playerUnits;
        IReadOnlyList<CharacterUnitModel> IInGameDebugReportRepository.EnemyUnitModels => _enemyUnits;
        IReadOnlyList<DeckUnitModel> IInGameDebugReportRepository.PlayerDeckUnits => _playerDeckUnits;
        IReadOnlyList<DeckUnitModel> IInGameDebugReportRepository.EnemyDeckUnits => _enemyDeckUnits;

        void IInGameDebugReportRepository.PushDamageReport(DebugInGameLogDamageModel damageModel)
        {
            // UnityEngine.Debug.Log($"=={damageModel} / {damageModel.GetHashCode()} / tick: {_currentTick}"); //仮に出すためのもの
            _damageReports.Add(damageModel);
            OnDamageReport?.Invoke(damageModel);
        }

        void IInGameDebugReportRepository.PushTickCount(long tickCount)
        {
            // UnityEngine.Debug.Log("==== " + tickCount + " ====");
            _currentTick = tickCount;
        }
        void IInGameDebugReportRepository.PushUnitModels(IReadOnlyList<CharacterUnitModel> models)
        {
            // foreach (var a in models)
            // {
            //     UnityEngine.Debug.Log("id : " + a.CharacterId + " / located : " + a.LocatedKomaId + " / prev:" + a.PrevLocatedKomaId);
            // }
            _playerUnits = models.Where(m => m.BattleSide == BattleSide.Player).ToList();
            _enemyUnits = models.Where(m => m.BattleSide == BattleSide.Enemy).ToList();
        }

        void IInGameDebugReportRepository.PushPlayerDeckUnits(IReadOnlyList<DeckUnitModel> playerDecks)
        {
            _playerDeckUnits = playerDecks;
        }

        void IInGameDebugReportRepository.PushEnemyDeckUnits(IReadOnlyList<DeckUnitModel> enemyDecks)
        {
            _enemyDeckUnits = enemyDecks;
        }

        void IInGameDebugReportRepository.Clear()
        {
            _damageReports.Clear();
        }
    }

}
#endif
