using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UserEmblem.Domain.UseCases
{
    public class GetUserEmblemBadgeUseCase
    {
        [Inject] IUserEmblemBadgeRepository UserEmblemBadgeRepository { get; }
        public List<MasterDataId> GetUserEmblemBadge()
        {
            return UserEmblemBadgeRepository.DisplayedUserEmblemIds;
        }
    }
}
