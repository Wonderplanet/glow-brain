using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UserEmblem.Domain.UseCases
{
    public class UpdateUserEmblemBadgeUseCase
    {
        [Inject] IUserEmblemBadgeRepository UserEmblemBadgeRepository { get; }
        public List<MasterDataId> UpdateUserEmblemBadge(List<MasterDataId> userEmblemIds)
        {
            UserEmblemBadgeRepository.DisplayedUserEmblemIds = userEmblemIds;

            return UserEmblemBadgeRepository.DisplayedUserEmblemIds;
        }
    }
}
