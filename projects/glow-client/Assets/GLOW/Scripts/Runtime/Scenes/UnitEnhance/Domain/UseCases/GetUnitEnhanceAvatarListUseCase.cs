using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Domain.UseCases
{
    public class GetUnitEnhanceAvatarListUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public UnitEnhanceAvatarListModel GetAvatarList(IReadOnlyList<UserDataId> userUnitList,
            UserDataId presentationUnitId)
        {
            var presentationMstUnitId = MasterDataId.Empty;
            var unitList = new List<MasterDataId>(userUnitList.Count);
            var userUnitModels = GameRepository.GetGameFetchOther().UserUnitModels;

            foreach(var userUnitId in userUnitList)
            {
                var userUnit = userUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
                unitList.Add(userUnit.MstUnitId);
                if (userUnit.UsrUnitId == presentationUnitId)
                {
                    presentationMstUnitId = userUnit.MstUnitId;
                }
            }

            return new UnitEnhanceAvatarListModel(
                unitList,
                presentationMstUnitId);
        }
    }
}
