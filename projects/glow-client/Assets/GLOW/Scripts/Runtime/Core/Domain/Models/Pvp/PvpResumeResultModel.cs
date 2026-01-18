namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpResumeResultModel(
        OpponentSelectStatusModel OpponentSelectStatus,
        OpponentPvpStatusModel OpponentPvpStatus
    )
    {
        public static PvpResumeResultModel Empty { get; } = new(
            OpponentSelectStatusModel.Empty,
            OpponentPvpStatusModel.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

