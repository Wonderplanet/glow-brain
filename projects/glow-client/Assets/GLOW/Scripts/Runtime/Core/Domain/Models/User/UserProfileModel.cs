using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserProfileModel(
        UserName Name,
        MasterDataId MstUnitId,
        MasterDataId MstEmblemId,
        DateTimeOffset? NameUpdateAt,
        MasterDataId MstAvatarId,
        MasterDataId MstAvatarFrameId,
        UserMyId MyId)
    {
        public static UserProfileModel Empty { get; } = new (
            UserName.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            null,
            MasterDataId.Empty,
            MasterDataId.Empty,
            UserMyId.Empty
        );
    }
}
