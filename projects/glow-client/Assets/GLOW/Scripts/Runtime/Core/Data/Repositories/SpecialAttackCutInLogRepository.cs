using System;
using System.Globalization;
using System.Linq;
using GLOW.Core.Data.Data.User;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Core.Data.Repositories
{
    public class SpecialAttackCutInLogRepository : ISpecialAttackCutInLogRepository
    {
        [Inject] ISpecialAttackCutInLogLocalDataStore SpecialAttackCutInLogLocalDataStore { get; }

        SpecialAttackCutInLogModel _specialAttackCutInLogModel = SpecialAttackCutInLogModel.Empty;

        void ISpecialAttackCutInLogRepository.Load()
        {
            var data = SpecialAttackCutInLogLocalDataStore.Load();
            if (data == null)
            {
                _specialAttackCutInLogModel = SpecialAttackCutInLogModel.Empty;
                return;
            }
            
            var specialAttackOnceADayDate = string.IsNullOrEmpty(data.SpecialAttackOnceADayDate)
                ? DateTimeOffset.MinValue
                : DateTimeOffset.Parse(data.SpecialAttackOnceADayDate, CultureInfo.InvariantCulture);
            
            var playedSpecialAttackUnitIds = data.PlayedSpecialAttackUnitIds
                .Select(id => new MasterDataId(id))
                .ToList();

            _specialAttackCutInLogModel = new SpecialAttackCutInLogModel(
                specialAttackOnceADayDate, 
                playedSpecialAttackUnitIds);
        }

        public void Save(SpecialAttackCutInLogModel specialAttackCutInLogModel)
        {
            _specialAttackCutInLogModel = specialAttackCutInLogModel;
            
            var specialAttackOnceADayDate = specialAttackCutInLogModel.SpecialAttackOnceADayDate.ToString(CultureInfo.InvariantCulture);
            
            var playedSpecialAttackUnitIds = specialAttackCutInLogModel.PlayedSpecialAttackUnitIds
                .Select(id => id.ToString())
                .ToList();
            
            var data = new SpecialAttackCutInLogData{
                SpecialAttackOnceADayDate = specialAttackOnceADayDate, 
                PlayedSpecialAttackUnitIds = playedSpecialAttackUnitIds
            };
            SpecialAttackCutInLogLocalDataStore.Save(data);
        }

        public SpecialAttackCutInLogModel Get()
        {
            return _specialAttackCutInLogModel;
        }
    }
}