using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using WonderPlanet.RandomGenerator;
using WonderPlanet.ResourceManagement;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Domain
{
    public class GetEventNotificationUseCase
    {
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IAssetSource AssetSource { get; }
        [Inject] IRandomizer Randomizer { get; }

        public EventBalloon GetEventBalloon()
        {
            var openingEvent = MstEventDataRepository.GetEvents()
                .Where(m =>CalculateTimeCalculator.IsValidTime(TimeProvider.Now, m.StartAt, m.EndAt))
                .OrderByDescending(m => m.StartAt)
                .FirstOrDefault();
            if (openingEvent == null) return EventBalloon.Empty;

            // selectでアセット無いユニットは弾いているが仮処理。全員アセット用意するのかアセット運用検討してから変更入る可能性あり
            var sameSeriesIdUnits = MstCharacterDataRepository.GetCharacters()
                .Where(m => m.MstSeriesId == openingEvent.MstSeriesId)
                .Select(m => EventUnitStandImageAssetPath.FromAssetKey(m.AssetKey))
                .Where(a => AssetSource.IsAddressExists(a.Value))
                .ToList();
            if (sameSeriesIdUnits.Count == 0) return EventBalloon.Empty;
            
            var targetIndex = Randomizer.Range(0, sameSeriesIdUnits.Count);

            var mstSeries = MstSeriesDataRepository.GetMstSeriesModel(openingEvent.MstSeriesId);
            var logoImagePath = new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(mstSeries.SeriesAssetKey.Value));

            return new EventBalloon(logoImagePath, sameSeriesIdUnits[targetIndex]);
        }
    }
}
