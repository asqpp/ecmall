<?php card_start('Quotations', true); ?>

<div class="flex justify-between items-center mb-6">
    <a href="<?php echo base_url('quotations/add'); ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Quotation
    </a>
</div>

<div class="overflow-x-auto">
    <?php table_start(['ID', 'Quotation#', 'Date', 'Customer', 'Total', 'Status', 'Actions']); ?>
        <?php if (empty($quotations)): ?>
            <tr><td colspan="7" class="text-center py-8 text-gray-500">No quotations found</td></tr>
        <?php else: ?>
            <?php foreach ($quotations as $quote): ?>
                <tr>
                    <td><?php echo $quote->quotation_id; ?></td>
                    <td><code><?php echo $quote->quotation_no; ?></code></td>
                    <td><?php echo format_date($quote->quotation_date); ?></td>
                    <td><strong><?php echo htmlspecialchars($quote->customer_name ?? 'N/A'); ?></strong></td>
                    <td class="font-semibold"><?php echo format_currency($quote->grand_total); ?></td>
                    <td>
                        <?php
                        if ($quote->status == 'converted') echo '<span class="badge badge-success">Converted</span>';
                        else if ($quote->status == 'rejected') echo '<span class="badge badge-danger">Rejected</span>';
                        else echo '<span class="badge badge-warning">Pending</span>';
                        ?>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?php echo base_url('quotations/view/' . $quote->quotation_id); ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <?php if ($quote->status == 'pending'): ?>
                                <a href="<?php echo base_url('quotations/convert_to_invoice/' . $quote->quotation_id); ?>"
                                   class="btn btn-sm btn-success"
                                   onclick="return confirm('Convert to invoice?')">
                                    <i class="fas fa-exchange-alt"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php table_end(); ?>
</div>

<?php card_end(); ?>
