using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EventQuestTop.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public class EventQuestTopUnitUseCaseModelFactory : IEventQuestTopUnitUseCaseModelFactory
    {
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        const int MaxDisplayUnitCount = 3;

        IReadOnlyList<EventQuestTopUnitUseCaseModel> IEventQuestTopUnitUseCaseModelFactory.Create(MasterDataId mstQuestId)
        {
            return MstQuestDataRepository.GetEventDisplayUnits()
                .Where(m => m.MstQuestId == mstQuestId)
                .Take(MaxDisplayUnitCount)
                .Join(
                    MstCharacterDataRepository.GetCharacters(),
                    m => m.MstUnitId,
                    c => c.Id,
                    (m, c) =>
                    {
                        var assetPath = UnitImageAssetPath.FromAssetKey(c.AssetKey);
                        var speechBalloons = new List<EventDisplayUnitSpeechBalloonText>();
                        if (!m.SpeechBalloonText1.IsEmpty())
                        {
                            speechBalloons.Add(m.SpeechBalloonText1);
                        }
                        if (!m.SpeechBalloonText2.IsEmpty())
                        {
                            speechBalloons.Add(m.SpeechBalloonText2);
                        }
                        if (!m.SpeechBalloonText3.IsEmpty())
                        {
                            speechBalloons.Add(m.SpeechBalloonText3);
                        }

                        return new EventQuestTopUnitUseCaseModel(assetPath,speechBalloons);
                    })
                .ToList();
        }
    }
}
