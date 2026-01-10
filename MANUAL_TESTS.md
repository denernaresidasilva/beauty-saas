# Testes manuais

## Agendamentos com horários sobrepostos

1. Acesse o painel da empresa e abra a agenda diária.
2. Crie um agendamento para o mesmo profissional, no mesmo dia, por exemplo:
   - Início: 10:00
   - Duração: 60 minutos
3. Tente criar um segundo agendamento para o mesmo profissional com horário que se sobreponha, por exemplo:
   - Início: 10:30
4. Confirme que o sistema bloqueia o segundo agendamento e exibe a mensagem de conflito.
5. (Opcional) Ajuste o filtro `beauty_appointment_conflict_statuses` para permitir/excluir o status `pendente` e repita o teste para validar o comportamento configurável.
