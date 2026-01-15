using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class SpecialAttackCutInLogInitializer : ISpecialAttackCutInLogInitializer
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] ISpecialAttackCutInLogRepository SpecialAttackCutInLogRepository { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }

        public SpecialAttackCutInLogInitializationResult Initialize()
        {
            SpecialAttackCutInLogRepository.Load();
            
            var specialAttackCutInLog = SpecialAttackCutInLogRepository.Get();

            DateTimeOffset specialAttackOnceADayDate = specialAttackCutInLog.SpecialAttackOnceADayDate;
            if (specialAttackCutInLog.IsEmpty() || DailyResetTimeCalculator.IsPastDailyRefreshTime(specialAttackOnceADayDate))
            {
                var updatedSpecialAttackCutInLog = new SpecialAttackCutInLogModel(
                    TimeProvider.Now, 
                    new List<MasterDataId>());
                
                SpecialAttackCutInLogRepository.Save(updatedSpecialAttackCutInLog);
                
                return new SpecialAttackCutInLogInitializationResult(updatedSpecialAttackCutInLog.PlayedSpecialAttackUnitIds);
            }

            return new SpecialAttackCutInLogInitializationResult(specialAttackCutInLog.PlayedSpecialAttackUnitIds);
        }
    }
}