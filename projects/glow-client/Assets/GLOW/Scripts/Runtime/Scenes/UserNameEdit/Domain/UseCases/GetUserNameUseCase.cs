using System;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UserNameEdit.Domain.Models;
using Zenject;

namespace GLOW.Scenes.UserNameEdit.Domain.UseCases
{
    public class GetUserNameUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public UserNameEditDialogModel GetUserName()
        {
            var userProfileModel = GameRepository.GetGameFetchOther().UserProfileModel;
            var userName = userProfileModel.Name;
            var nameUpdateAt = userProfileModel.NameUpdateAt;

            bool isCanChangeName = true;
            TimeSpan remainingTimeSpan = TimeSpan.Zero;
            if (nameUpdateAt != null)
            {
                var currentUpdateDate = TimeProvider.Now;
                if (nameUpdateAt.Value.AddDays(1) > currentUpdateDate)
                {
                    isCanChangeName = false;
                    remainingTimeSpan = nameUpdateAt.Value.AddDays(1) - currentUpdateDate;
                }
            }

            return new UserNameEditDialogModel(userName, isCanChangeName, new RemainingTimeSpan(remainingTimeSpan));
        }
    }
}
