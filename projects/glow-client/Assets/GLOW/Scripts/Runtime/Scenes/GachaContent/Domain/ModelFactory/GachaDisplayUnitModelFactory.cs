using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.GachaContent.Domain.Model;
using Zenject;

namespace GLOW.Scenes.GachaContent.Domain
{
    public class GachaDisplayUnitModelFactory : IGachaDisplayUnitModelFactory
    {
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public List<GachaDisplayUnitModel> CreateGachaDisplayUnitModels(MasterDataId gachaId)
        {
            var gachaDisplayUnitModels = OprGachaRepository.GetOprGachaDisplayUnitI18nModelsById(gachaId);

            var gachaUnitInfoUseCaseModels = new List<GachaDisplayUnitModel>();
            foreach (var model in gachaDisplayUnitModels)
            {
                var mstUnit = MstCharacterDataRepository.GetCharacter(model.PickupMstUnitId);
                gachaUnitInfoUseCaseModels.Add(new GachaDisplayUnitModel(
                    mstUnit.Id,
                    mstUnit.Name,
                    mstUnit.RoleType,
                    mstUnit.Color,
                    mstUnit.Rarity,
                    new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(mstUnit.SeriesAssetKey.Value)),
                    GachaContentCutInAssetPath.FromAssetKey(mstUnit.AssetKey),
                    model.GachaDisplayUnitDescription
                ));
            }

            return gachaUnitInfoUseCaseModels;
        }
    }
}
