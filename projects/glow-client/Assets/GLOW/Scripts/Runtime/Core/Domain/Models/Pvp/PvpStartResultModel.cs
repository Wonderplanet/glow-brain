namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpStartResultModel(
        OpponentPvpStatusModel OpponentPvpStatus
    )
    {
        public static PvpStartResultModel Empty { get; } = new(OpponentPvpStatusModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
