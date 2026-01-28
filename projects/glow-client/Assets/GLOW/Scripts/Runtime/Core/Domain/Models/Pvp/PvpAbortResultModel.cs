using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpAbortResultModel(
        UserPvpStatusModel UserPvpStatus,
        IReadOnlyList<UserItemModel> UserItems
    )
    {
        public static PvpAbortResultModel Empty { get; } = new(
            UserPvpStatusModel.Empty,
            new List<UserItemModel>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}