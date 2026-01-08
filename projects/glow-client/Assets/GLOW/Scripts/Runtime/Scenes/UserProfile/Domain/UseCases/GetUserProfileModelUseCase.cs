using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UserProfile.Domain.Models;
using Zenject;

namespace GLOW.Scenes.UserProfile.Domain.UseCases
{
    public class GetUserProfileModelUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IUserProfileBadgeRepository UserProfileBadgeRepository { get; }

        public UserProfileModel GetUserProfileModel()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userUnits = gameFetchOther.UserUnitModels;
            var mstUnits = userUnits
                .Select(unit => MstCharacterDataRepository.GetCharacter(unit.MstUnitId))
                .ToList();
            var userProfile = gameFetchOther.UserProfileModel;

            var mstCurrentAvatarIcon = mstUnits
                .FirstOrDefault(mstUnit => mstUnit.Id == userProfile.MstUnitId) ?? mstUnits.First();

            var displayAvatarList = UserProfileBadgeRepository.DisplayedUserProfileAvatarIds;

            var currentAvatarCellModel = new UserProfileAvatarCellModel(
                mstCurrentAvatarIcon.Id,
                CharacterIconAssetPath.FromAssetKey(mstCurrentAvatarIcon.AssetKey),
                new NotificationBadge(false));

            var avatarIconList = mstUnits.Select(mstUnit => new UserProfileAvatarCellModel(
                mstUnit.Id,
                CharacterIconAssetPath.FromAssetKey(mstUnit.AssetKey),
                new NotificationBadge(displayAvatarList.All(displayed => displayed != mstUnit.Id)))).ToList();

            return new UserProfileModel(
                userProfile.Name,
                userProfile.MyId,
                currentAvatarCellModel,
                avatarIconList);
        }
    }
}
