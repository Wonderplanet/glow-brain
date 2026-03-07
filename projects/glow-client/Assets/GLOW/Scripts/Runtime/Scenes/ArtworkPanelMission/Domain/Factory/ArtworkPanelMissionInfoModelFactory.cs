using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkPanelMission.Domain.Model;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Factory
{
    public class ArtworkPanelMissionInfoModelFactory : IArtworkPanelMissionInfoModelFactory
    {
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IArtworkPanelHelper ArtworkPanelHelper { get; }
        
        ArtworkPanelMissionInfoModel IArtworkPanelMissionInfoModelFactory.CreateBySelectedMstEventId(
            MasterDataId selectedMstEventId,
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels)
        {
            var selectedEventModel = MstEventDataRepository.GetEventFirstOrDefault(selectedMstEventId);
            if (selectedEventModel.IsEmpty()) return ArtworkPanelMissionInfoModel.Empty;
            
            return Create(
                selectedEventModel,
                userArtworkModels,
                userArtworkFragmentModels);
        }

        ArtworkPanelMissionInfoModel IArtworkPanelMissionInfoModelFactory.CreateByLatestMstEventId(
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels)
        {
            var now = TimeProvider.Now;
            var latestMstEventModel = MstEventDataRepository.GetEvents()
                .Where(model => CalculateTimeCalculator.IsValidTime(now, model.StartAt, model.EndAt))
                .MaxBy(model => model.StartAt) ?? MstEventModel.Empty;
            
            return Create(
                latestMstEventModel,
                userArtworkModels,
                userArtworkFragmentModels);
        }

        ArtworkPanelMissionInfoModel Create(
            MstEventModel selectedEventModel,
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels)
        {
            var mstArtworkPanelMissionModel = MissionDataRepository.GetMstArtworkPanelMissionModels(
                    selectedEventModel.Id)
                .FirstOrDefault(MstArtworkPanelMissionModel.Empty);
            
            if (mstArtworkPanelMissionModel.IsEmpty()) return ArtworkPanelMissionInfoModel.Empty;
            
            var mstArtwork = MstArtworkDataRepository.GetArtwork(
                mstArtworkPanelMissionModel.MstArtworkId);
            var artworkPanelModel = ArtworkPanelHelper.CreateArtworkPanelModel(
                mstArtwork,
                userArtworkModels,
                userArtworkFragmentModels);
            
            var remainingTimeSpan = CalculateTimeCalculator.GetRemainingTime(
                TimeProvider.Now,
                mstArtworkPanelMissionModel.EndDate.Value);

            return new ArtworkPanelMissionInfoModel(
                mstArtworkPanelMissionModel.Id,
                selectedEventModel.Id,
                artworkPanelModel,
                remainingTimeSpan);
        }
    }
}