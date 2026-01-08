#if GLOW_INGAME_DEBUG
using System;
using System.Collections.Generic;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Repositories.Debug;
using Zenject;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    public sealed class DebugIngameLogViewerUseCase
    {
        [Inject] IInGameDebugReportRepository InGameDebugReportRepository { get; }

        public void SubscribeDamageReport(Action<DebugInGameLogDamageModel> onDamage)
        {
            InGameDebugReportRepository.OnDamageReport = onDamage;
        }
        public void UnSubscribeDamageReport()
        {
            InGameDebugReportRepository.OnDamageReport = null;
        }
        public IReadOnlyList<CharacterUnitModel> GetUnitModels()
        {
            return InGameDebugReportRepository.PlayerUnitModels;
        }

        public IReadOnlyList<CharacterUnitModel> GetEnemyModels()
        {
            return InGameDebugReportRepository.EnemyUnitModels;
        }
        public IReadOnlyList<DebugInGameLogDamageModel> GetDamageReports()
        {
            return InGameDebugReportRepository.DamageReports;
        }

        public int GetCurrentTickCount()
        {
            return InGameDebugReportRepository.CurrentTick;
        }

        public IReadOnlyList<DeckUnitModel> GetPlayerDeckModels()
        {
            return InGameDebugReportRepository.PlayerDeckUnits;
        }

        public IReadOnlyList<DeckUnitModel> GetEnemyDeckModels()
        {
            return InGameDebugReportRepository.EnemyDeckUnits;
        }
    }
}
#endif
